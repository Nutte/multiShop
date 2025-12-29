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

class OrderManagementController extends Controller
{
    protected OrderService $orderService;
    protected TenantService $tenantService;

    public function __construct(OrderService $orderService, TenantService $tenantService)
    {
        $this->orderService = $orderService;
        $this->tenantService = $tenantService;
    }

    /**
     * Список заказов
     */
    public function index(Request $request)
    {
        $currentTenantId = $this->resolveTenantContext($request);
        
        if ($currentTenantId) {
            $orders = $this->getTenantOrders($currentTenantId);
            return view('admin.orders.index', compact('orders', 'currentTenantId'));
        }

        // Для супер-админа: агрегация заказов из всех тенантов
        $orders = $this->getAllTenantsOrders($request);
        return view('admin.orders.index', ['orders' => $orders, 'currentTenantId' => null]);
    }

    /**
     * Просмотр заказа
     */
    public function show(Request $request, $id)
    {
        $tenantId = $this->resolveTenantIdForShow($request);
        
        if (!$tenantId) {
            return back()->with('error', 'Tenant context required.');
        }

        $this->tenantService->switchTenant($tenantId);
        $order = Order::with(['items', 'user'])->findOrFail($id);
        
        return view('admin.orders.show', [
            'order' => $order,
            'currentTenantId' => $tenantId
        ]);
    }

    /**
     * Создание заказа (форма)
     */
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

    /**
     * Сохранение нового заказа
     */
    public function store(Request $request)
    {
        $tenantId = $request->input('tenant_id');
        if ($tenantId) {
            $this->tenantService->switchTenant($tenantId);
        }

        $validated = $this->validateOrderRequest($request);

        // Если выбран пользователь, берем данные из профиля
        if (!empty($validated['user_id'])) {
            $this->syncCustomerDataFromUser($validated);
        }

        $this->orderService->createOrder($validated);

        return redirect()->route('admin.orders.index', ['tenant_id' => $validated['tenant_id']])
            ->with('success', 'Order created successfully.');
    }

    /**
     * Редактирование заказа (форма)
     */
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

    /**
     * Обновление заказа
     */
    public function update(Request $request, $id)
    {
        $tenantId = $request->get('tenant_id');
        $this->tenantService->switchTenant($tenantId);
        
        $order = Order::with('items')->findOrFail($id);

        if ($request->input('update_mode') === 'status_only') {
            return $this->updateOrderStatusOnly($request, $order);
        }

        $validated = $this->validateOrderUpdateRequest($request);

        // Если выбран пользователь, берем данные из профиля
        if (!empty($validated['user_id'])) {
            $this->syncCustomerDataFromUser($validated);
        }

        $items = $request->has('items') ? $request->input('items') : null;
        $this->orderService->updateOrder($order, $validated, $items);

        return redirect()->route('admin.orders.index', ['tenant_id' => $tenantId])
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Вспомогательные методы
     */

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
                \Log::warning("Failed to load orders for tenant {$tenantId}: " . $e->getMessage());
                continue;
            }
        }

        $sortedOrders = $allOrders->sortByDesc('created_at')->values();
        
        return new LengthAwarePaginator(
            $sortedOrders->forPage($request->get('page', 1), 20),
            $sortedOrders->count(),
            20,
            $request->get('page', 1),
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
        
        if ($user->role === 'super_admin') {
            return $request->get('tenant_id');
        }
        
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

        if ($tenantId) {
            try {
                $this->tenantService->switchTenant($tenantId);
                
                // Загрузка товаров
                $products = Product::with('variants')->where('stock_quantity', '>', 0)->get();
                foreach ($products as $prod) {
                    $prodData = $prod->toArray();
                    $prodData['tenant_id'] = $tenantId;
                    $prodData['tenant_name'] = config("tenants.tenants.{$tenantId}.name");
                    $prodData['variants'] = $prod->variants->toArray();
                    $productsCatalog[] = $prodData;
                }

                // Загрузка клиентов
                $customers = User::where('role', 'client')
                    ->select('id', 'name', 'phone', 'email')
                    ->orderBy('name')
                    ->get();

            } catch (\Exception $e) {
                \Log::error("Failed to load form data for tenant {$tenantId}: " . $e->getMessage());
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
            'user_id' => 'nullable|exists:users,id',
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
            'user_id' => 'nullable|exists:users,id',
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
    /**
 * Отправка уведомления о заказе
 */
public function sendNotification(Request $request, $id)
{
    $tenantId = $request->get('tenant_id');
    if (!$tenantId) {
        $tenantId = $this->tenantService->getCurrentTenantId();
    }
    
    if (!$tenantId) {
        return back()->with('error', 'Tenant context required.');
    }

    $this->tenantService->switchTenant($tenantId);
    $order = Order::with('items')->findOrFail($id);

    // Получаем настройки Telegram для текущего магазина
    $telegramSettings = \App\Models\TelegramSettings::where('tenant_id', $tenantId)->first();

    if (!$telegramSettings || !$telegramSettings->bot_token || !$telegramSettings->chat_id) {
        return back()->with('error', 'Telegram settings not configured.');
    }

    // Формируем сообщение
    $message = "Новый заказ #{$order->order_number}\n";
    $message .= "Клиент: {$order->customer_name}\n";
    $message .= "Телефон: {$order->customer_phone}\n";
    $message .= "Сумма: {$order->total_amount} руб.\n";
    $message .= "Способ доставки: {$order->shipping_method}\n";
    $message .= "Адрес: {$order->shipping_address}\n";

    // Отправляем в Telegram
    try {
        $telegram = new \TelegramBot\Api\BotApi($telegramSettings->bot_token);
        $telegram->sendMessage($telegramSettings->chat_id, $message);
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to send notification: ' . $e->getMessage());
    }

    return back()->with('success', 'Notification sent successfully.');
}
}   

