<?php
// File - app/Http/Controllers/Admin/OrderController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    private function resolveContext(Request $request)
    {
        if (auth()->user()->role === 'super_admin') {
            $tenantId = $request->get('tenant_id');
            if ($tenantId) {
                $this->tenantService->switchTenant($tenantId);
                return $tenantId;
            }
            return null;
        }
        return $this->tenantService->getCurrentTenantId();
    }

    public function index(Request $request)
    {
        $currentTenantId = $this->resolveContext($request);
        
        if ($currentTenantId) {
            $orders = Order::latest()->paginate(20);
            $tenantName = config("tenants.tenants.{$currentTenantId}.name");
            $orders->getCollection()->transform(function ($order) use ($currentTenantId, $tenantName) {
                $order->tenant_id = $currentTenantId;
                $order->tenant_name = $tenantName;
                return $order;
            });
            return view('admin.orders.index', compact('orders', 'currentTenantId'));
        }

        $allOrders = collect();
        foreach (config('tenants.tenants') as $tenantId => $config) {
            try {
                $this->tenantService->switchTenant($tenantId);
                $tenantOrders = Order::latest()->limit(50)->get();
                foreach ($tenantOrders as $order) {
                    $order->tenant_id = $tenantId;
                    $order->tenant_name = $config['name'];
                    $allOrders->push($order);
                }
            } catch (\Exception $e) { continue; }
        }

        $sortedOrders = $allOrders->sortByDesc('created_at')->values();
        $paginatedOrders = new LengthAwarePaginator(
            $sortedOrders->forPage($request->get('page', 1), 20),
            $sortedOrders->count(),
            20,
            $request->get('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.orders.index', ['orders' => $paginatedOrders, 'currentTenantId' => null]);
    }

    public function show(Request $request, $id)
    {
        $tenantId = $request->get('tenant_id') ?? $this->tenantService->getCurrentTenantId();
        if (!$tenantId) return back()->with('error', 'Tenant context required.');

        $this->tenantService->switchTenant($tenantId);
        $order = Order::with('items')->findOrFail($id);
        
        return view('admin.orders.show', ['order' => $order, 'currentTenantId' => $tenantId]);
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->role === 'super_admin';
        $currentTenantId = $request->get('tenant_id');

        if (!$isSuperAdmin) {
            $currentTenantId = $this->tenantService->getCurrentTenantId();
        }

        $productsCatalog = [];

        if ($currentTenantId) {
            try {
                $this->tenantService->switchTenant($currentTenantId);
                $products = Product::with('variants')->where('stock_quantity', '>', 0)->get();
                
                foreach ($products as $prod) {
                    $prodData = $prod->toArray();
                    $prodData['tenant_id'] = $currentTenantId;
                    $prodData['tenant_name'] = config("tenants.tenants.{$currentTenantId}.name");
                    $prodData['variants'] = $prod->variants->toArray();
                    $productsCatalog[] = $prodData;
                }
            } catch (\Exception $e) {}
        }

        return view('admin.orders.form', [
            'order' => null,
            'productsJson' => $productsCatalog,
            'isSuperAdmin' => $isSuperAdmin,
            'currentTenantId' => $currentTenantId
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|string',
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_email' => 'nullable|email',
            'shipping_method' => 'required|string',
            'shipping_address' => 'required|string',
            'is_instagram' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'items.*.size' => 'nullable', // Разрешаем null, обработаем в saveOrderItems
        ]);

        $this->tenantService->switchTenant($validated['tenant_id']);

        DB::transaction(function () use ($validated) {
            $total = 0;
            foreach ($validated['items'] as $item) $total += $item['price'] * $item['quantity'];

            // Безопасное получение email
            $email = !empty($validated['customer_email']) ? $validated['customer_email'] : null;

            $order = Order::create([
                'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $email,
                'shipping_method' => $validated['shipping_method'],
                'shipping_address' => $validated['shipping_address'],
                'status' => 'new',
                'subtotal' => $total,
                'total_amount' => $total,
                'is_instagram' => $validated['is_instagram'] ?? false,
            ]);

            $this->saveOrderItems($order, $validated['items']);
        });

        return redirect()->route('admin.orders.index', ['tenant_id' => $validated['tenant_id']])->with('success', 'Order created.');
    }

    public function edit(Request $request, $id)
    {
        $tenantId = $request->get('tenant_id');
        if (!$tenantId) $tenantId = $this->tenantService->getCurrentTenantId();

        $this->tenantService->switchTenant($tenantId);
        $order = Order::with('items')->findOrFail($id);
        
        $products = Product::with('variants')->get();
        $productsCatalog = [];
        foreach ($products as $prod) {
            $prodData = $prod->toArray();
            $prodData['tenant_id'] = $tenantId;
            $productsCatalog[] = $prodData;
        }

        return view('admin.orders.form', [
            'order' => $order,
            'productsJson' => $productsCatalog,
            'isSuperAdmin' => auth()->user()->role === 'super_admin',
            'currentTenantId' => $tenantId
        ]);
    }

    public function update(Request $request, $id)
    {
        $tenantId = $request->get('tenant_id');
        $this->tenantService->switchTenant($tenantId);
        $order = Order::with('items')->findOrFail($id);

        if ($request->input('update_mode') === 'status_only') {
            $validated = $request->validate([
                'status' => 'required|string',
                'is_instagram' => 'boolean',
            ]);
            $this->handleStatusChangeStock($order, $validated['status']);
            $order->update([
                'status' => $validated['status'],
                'is_instagram' => $request->has('is_instagram') ? 1 : 0
            ]);
            return back()->with('success', 'Status updated successfully.');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_email' => 'nullable|email',
            'shipping_method' => 'required|string',
            'shipping_address' => 'required|string',
            'status' => 'required|string',
            'is_instagram' => 'boolean',
            'items' => 'nullable|array', 
        ]);

        DB::transaction(function () use ($order, $validated, $request) {
            if ($request->has('items') && count($request->input('items')) > 0) {
                // Возврат старых товаров на склад перед удалением
                foreach ($order->items as $oldItem) {
                    $this->adjustStock($oldItem->product_id, $oldItem->size, $oldItem->quantity, 'increment');
                }
                $order->items()->delete();
                
                $total = $this->saveOrderItems($order, $request->input('items'));
                $order->update(['subtotal' => $total, 'total_amount' => $total]);
            }

            $this->handleStatusChangeStock($order, $validated['status']);

            $email = !empty($validated['customer_email']) ? $validated['customer_email'] : $order->customer_email;

            $order->update([
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'customer_email' => $email,
                'shipping_method' => $validated['shipping_method'],
                'shipping_address' => $validated['shipping_address'],
                'status' => $validated['status'],
                'is_instagram' => $request->has('is_instagram') ? 1 : 0,
            ]);
        });

        return redirect()->route('admin.orders.index', ['tenant_id' => $tenantId])->with('success', 'Order updated.');
    }

    private function handleStatusChangeStock($order, $newStatus)
    {
        if ($newStatus === 'cancelled' && $order->status !== 'cancelled') {
             foreach ($order->items as $item) $this->adjustStock($item->product_id, $item->size, $item->quantity, 'increment');
        }
        if ($order->status === 'cancelled' && $newStatus !== 'cancelled') {
             foreach ($order->items as $item) $this->adjustStock($item->product_id, $item->size, $item->quantity, 'decrement');
        }
    }

    // ВАЖНО: Исправленная логика сохранения
    private function saveOrderItems($order, $itemsData) {
        $total = 0;
        foreach ($itemsData as $data) {
            $product = Product::with('variants')->find($data['product_id']);
            if (!$product) continue;

            $lineTotal = $data['price'] * $data['quantity'];
            $total += $lineTotal;

            // Логика определения размера:
            // 1. Если у товара НЕТ вариантов -> размер 'One Size', даже если пришло что-то другое
            // 2. Если у товара ЕСТЬ варианты -> берем переданный размер. Если не передан - ошибка или дефолт
            $hasVariants = $product->variants->isNotEmpty();
            
            if ($hasVariants) {
                $size = !empty($data['size']) ? $data['size'] : null;
                // Если размер обязателен, но не передан - можно либо кинуть ошибку, либо взять первый доступный
                if (!$size) $size = $product->variants->first()->size;
            } else {
                $size = 'One Size';
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'size' => $size,
                'quantity' => $data['quantity'],
                'price' => $data['price'],
                'total' => $lineTotal,
            ]);
            
            // Списываем сток
            $this->adjustStock($product, $size, $data['quantity'], 'decrement');
        }
        return $total;
    }

    // Принимает либо ID, либо объект Product
    private function adjustStock($productOrId, $size, $qty, $action) {
        $product = ($productOrId instanceof Product) ? $productOrId : Product::find($productOrId);
        if (!$product) return;

        $method = $action === 'increment' ? 'increment' : 'decrement';
        
        // 1. Общий сток товара
        $product->$method('stock_quantity', $qty);
        
        // 2. Сток варианта (если есть)
        if ($size && $size !== 'One Size') {
            $v = $product->variants()->where('size', $size)->first();
            if ($v) $v->$method('stock', $qty);
        }
    }
}