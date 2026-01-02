<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule; // ВАЖНО: Добавлен импорт Rule

class OrderManagementController extends Controller
{
    protected OrderService $orderService;
    protected TenantService $tenantService;

    public function __construct(OrderService $orderService, TenantService $tenantService)
    {
        $this->orderService = $orderService;
        $this->tenantService = $tenantService;
    }

    public function index(Request $request)
    {
        $currentTenantId = $this->resolveTenantContext($request);
        
        if ($currentTenantId) {
            $orders = $this->getTenantOrders($currentTenantId);
            return view('admin.orders.index', compact('orders', 'currentTenantId'));
        }

        $orders = $this->getAllTenantsOrders($request);
        return view('admin.orders.index', ['orders' => $orders, 'currentTenantId' => null]);
    }

    public function show(Request $request, $id)
    {
        $tenantId = $this->resolveTenantIdForShow($request);
        if (!$tenantId) return back()->with('error', 'Tenant context required.');

        $this->tenantService->switchTenant($tenantId);
        
        $order = Order::with(['items'])->findOrFail($id);
        
        // Подгружаем пользователя. Так как User теперь явно public.users, связь должна работать лучше,
        // но оставим проверку для надежности.
        if ($order->user_id && !$order->relationLoaded('user')) {
             $user = User::find($order->user_id);
             $order->setRelation('user', $user);
        }
        
        return view('admin.orders.show', [
            'order' => $order,
            'currentTenantId' => $tenantId
        ]);
    }

    public function create(Request $request)
    {
        $currentTenantId = $this->resolveTenantIdForCreate($request);
        $isSuperAdmin = auth()->user()->role === 'super_admin';

        $data = $this->prepareOrderFormData($currentTenantId);
        
        return view('admin.orders.form', array_merge($data, [
            'order' => null,
            'isSuperAdmin' => $isSuperAdmin,
            'currentTenantId' => $currentTenantId
        ]));
    }

    public function store(Request $request)
    {
        $tenantId = $request->input('tenant_id');
        if ($tenantId) {
            $this->tenantService->switchTenant($tenantId);
        }

        $validated = $this->validateOrderRequest($request);

        if (!empty($validated['user_id'])) {
            $this->syncCustomerDataFromUser($validated);
        }

        $this->orderService->createOrder($validated);

        return redirect()->route('admin.orders.index', ['tenant_id' => $validated['tenant_id']])
            ->with('success', 'Order created successfully.');
    }

    public function edit(Request $request, $id)
    {
        $tenantId = $this->resolveTenantIdForEdit($request);
        $this->tenantService->switchTenant($tenantId);

        $order = Order::with('items')->findOrFail($id);
        $data = $this->prepareOrderFormData($tenantId);
        
        return view('admin.orders.form', array_merge($data, [
            'order' => $order,
            'isSuperAdmin' => auth()->user()->role === 'super_admin',
            'currentTenantId' => $tenantId
        ]));
    }

    public function update(Request $request, $id)
    {
        $tenantId = $request->get('tenant_id');
        $this->tenantService->switchTenant($tenantId);
        
        $order = Order::with('items')->findOrFail($id);

        if ($request->input('update_mode') === 'status_only') {
            return $this->updateOrderStatusOnly($request, $order);
        }

        $validated = $this->validateOrderUpdateRequest($request);

        if (!empty($validated['user_id'])) {
             $this->syncCustomerDataFromUser($validated);
        }

        $items = $request->has('items') ? $request->input('items') : null;
        $this->orderService->updateOrder($order, $validated, $items);

        return redirect()->route('admin.orders.index', ['tenant_id' => $tenantId])
            ->with('success', 'Order updated successfully.');
    }

    // --- Helpers ---

    protected function resolveTenantContext(Request $request): ?string
    {
        if (auth()->user()->role === 'super_admin') {
            return $request->get('tenant_id');
        }
        return $this->tenantService->getCurrentTenantId();
    }

    protected function getTenantOrders(string $tenantId)
    {
        $this->tenantService->switchTenant($tenantId);
        $orders = Order::latest()->paginate(20);
        
        $tenantName = config("tenants.tenants.{$tenantId}.name");
        $orders->getCollection()->transform(function ($order) use ($tenantId, $tenantName) {
            $order->tenant_id = $tenantId;
            $order->tenant_name = $tenantName;
            return $order;
        });
        
        return $orders;
    }

    protected function getAllTenantsOrders(Request $request)
    {
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
            } catch (\Exception $e) {
                \Log::warning("Tenant error {$tenantId}: " . $e->getMessage());
                continue;
            }
        }
        $sorted = $allOrders->sortByDesc('created_at')->values();
        return new LengthAwarePaginator(
            $sorted->forPage($request->get('page', 1), 20),
            $sorted->count(), 20, $request->get('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    protected function resolveTenantIdForShow(Request $request): ?string
    {
        return $request->get('tenant_id') ?? $this->tenantService->getCurrentTenantId();
    }

    protected function resolveTenantIdForCreate(Request $request): ?string
    {
        $user = auth()->user();
        if ($user->role === 'super_admin') return $request->get('tenant_id');
        return $this->tenantService->getCurrentTenantId();
    }

    protected function resolveTenantIdForEdit(Request $request): string
    {
        return $request->get('tenant_id') ?? $this->tenantService->getCurrentTenantId();
    }

    protected function prepareOrderFormData(?string $tenantId): array
    {
        $productsCatalog = [];
        $customers = [];

        // 1. Грузим пользователей из PUBLIC схемы.
        // Так как мы добавили $table='public.users' в модель,
        // теперь User::get() всегда вернет данные из паблика, даже без переключения схемы.
        // Но для надежности и соблюдения логики предыдущих шагов оставим как есть.
        try {
            DB::statement('SET search_path TO public');
            
            $customers = User::whereIn('role', ['client', 'admin', 'manager', 'super_admin'])
                ->select('id', 'name', 'phone', 'email', 'role')
                ->orderBy('name')
                ->get();
                
        } catch (\Exception $e) {
             \Log::error("Failed to load users: " . $e->getMessage());
        }

        // 2. Грузим товары из TENANT схемы
        if ($tenantId) {
            try {
                $this->tenantService->switchTenant($tenantId);
                
                $products = Product::with('variants')->where('stock_quantity', '>', 0)->get();
                foreach ($products as $prod) {
                    $prodData = $prod->toArray();
                    $prodData['tenant_id'] = $tenantId;
                    $prodData['tenant_name'] = config("tenants.tenants.{$tenantId}.name");
                    $prodData['variants'] = $prod->variants->toArray();
                    $productsCatalog[] = $prodData;
                }
            } catch (\Exception $e) {
                \Log::error("Tenant data error {$tenantId}: " . $e->getMessage());
            }
        }

        return [
            'productsJson' => $productsCatalog,
            'customersJson' => $customers 
        ];
    }

    protected function validateOrderRequest(Request $request): array
    {
        return $request->validate([
            'tenant_id' => 'required|string',
            // ИСПРАВЛЕНИЕ: Используем Rule::exists для корректного определения таблицы и схемы
            'user_id' => ['nullable', Rule::exists(User::class, 'id')],
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
            'items.*.size' => 'nullable',
        ]);
    }

    protected function validateOrderUpdateRequest(Request $request): array
    {
        return $request->validate([
            // ИСПРАВЛЕНИЕ: Используем Rule::exists. Исправляет ошибку "Database connection [public] not configured"
            'user_id' => ['nullable', Rule::exists(User::class, 'id')],
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_email' => 'nullable|email',
            'shipping_method' => 'required|string',
            'shipping_address' => 'required|string',
            'status' => 'required|string',
            'is_instagram' => 'boolean',
            'items' => 'nullable|array',
        ]);
    }

    protected function syncCustomerDataFromUser(array &$validatedData): void
    {
        $user = User::find($validatedData['user_id']);
        if ($user) {
            $validatedData['customer_name'] = $user->name;
            $validatedData['customer_phone'] = $user->phone;
            $validatedData['customer_email'] = $user->email;
        }
    }

    protected function updateOrderStatusOnly(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'is_instagram' => 'boolean',
        ]);
        
        $this->orderService->updateOrderStatus(
            $order, 
            $validated['status'], 
            $request->has('is_instagram')
        );

        return back()->with('success', 'Status updated successfully.');
    }
    
    public function sendNotification(Request $request, $id)
    {
        $tenantId = $request->get('tenant_id') ?? $this->tenantService->getCurrentTenantId();
        
        if (!$tenantId) return back()->with('error', 'Tenant context required.');

        $this->tenantService->switchTenant($tenantId);
        $order = Order::with('items')->findOrFail($id);

        $telegramSettings = \App\Models\TelegramSettings::where('tenant_id', $tenantId)->first();

        if (!$telegramSettings || !$telegramSettings->bot_token || !$telegramSettings->chat_id) {
            return back()->with('error', 'Telegram settings not configured.');
        }

        $message = "Новый заказ #{$order->order_number}\n";
        $message .= "Клиент: {$order->customer_name}\n";
        $message .= "Телефон: {$order->customer_phone}\n";
        $message .= "Сумма: {$order->total_amount} грн.\n";
        $message .= "Способ доставки: {$order->shipping_method}\n";
        $message .= "Адрес: {$order->shipping_address}\n";

        try {
            $telegram = new \TelegramBot\Api\BotApi($telegramSettings->bot_token);
            $telegram->sendMessage($telegramSettings->chat_id, $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Telegram error: ' . $e->getMessage());
        }

        return back()->with('success', 'Notification sent successfully.');
    }
}