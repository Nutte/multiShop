<?php
// FILE: app/Http/Controllers/Admin/OrderController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
            // Если tenant_id передан как пустая строка (из фильтра "All Shops"), считаем это null
            return $tenantId ?: null;
        }
        return $this->tenantService->getCurrentTenantId();
    }

    public function index(Request $request)
    {
        $currentTenantId = $this->resolveContext($request);
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $orders = new Collection();

        if ($isSuperAdmin && !$currentTenantId) {
            // --- ГЛОБАЛЬНЫЙ РЕЖИМ: СОБИРАЕМ СО ВСЕХ МАГАЗИНОВ ---
            foreach (config('tenants.tenants') as $id => $config) {
                try {
                    $this->tenantService->switchTenant($id);
                    $query = Order::query();

                    if ($request->filled('search')) {
                        $query->where(function($q) use ($request) {
                            $q->where('order_number', 'like', "%{$request->search}%")
                              ->orWhere('customer_phone', 'like', "%{$request->search}%");
                        });
                    }

                    if ($request->filled('status')) {
                        $query->where('status', $request->status);
                    }

                    // Берем по 20 последних с каждого магазина
                    $storeOrders = $query->latest()->take(20)->get();
                    
                    // Помечаем, откуда заказ
                    $storeOrders->each(function($o) use ($config, $id) {
                        $o->tenant_name = $config['name'];
                        $o->tenant_id = $id; 
                    });
                    
                    $orders = $orders->merge($storeOrders);
                } catch (\Exception $e) { continue; }
            }

            // Сортируем общий список по дате
            $orders = $orders->sortByDesc('created_at');

            // Ручная пагинация для коллекции
            $page = $request->get('page', 1);
            $perPage = 20;
            $orders = new LengthAwarePaginator(
                $orders->forPage($page, $perPage),
                $orders->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

        } else {
            // --- ОБЫЧНЫЙ РЕЖИМ (ОДИН МАГАЗИН) ---
            if ($currentTenantId) {
                $this->tenantService->switchTenant($currentTenantId);
            }
            
            $query = Order::query();

            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('order_number', 'like', "%{$request->search}%")
                      ->orWhere('customer_phone', 'like', "%{$request->search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $orders = $query->latest()->paginate(20);
        }

        return view('admin.orders.index', compact('orders', 'currentTenantId'));
    }

    public function show(Request $request, $id)
    {
        // Для просмотра заказа нам ОБЯЗАТЕЛЬНО нужно знать, в какой он базе.
        // Если tenant_id пришел в запросе (из глобальной таблицы), используем его.
        $tenantId = $request->get('tenant_id') ?? $this->resolveContext($request);
        
        if (!$tenantId && auth()->user()->role === 'super_admin') {
            // Если супер-админ пытается открыть заказ без контекста, это ошибка,
            // но мы можем попробовать найти заказ перебором (медленно, но надежно)
            foreach (config('tenants.tenants') as $tid => $tconfig) {
                $this->tenantService->switchTenant($tid);
                $order = Order::with('items')->find($id);
                if ($order) {
                    $tenantId = $tid;
                    break;
                }
            }
        } else {
             $this->tenantService->switchTenant($tenantId);
        }

        $order = Order::with('items')->findOrFail($id);
        
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, $id)
    {
        // Аналогично show, нам нужен контекст
        $tenantId = $request->get('tenant_id') ?? $this->resolveContext($request);
        $this->tenantService->switchTenant($tenantId);

        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:new,processing,shipped,completed,cancelled'
        ]);

        $order->update(['status' => $validated['status']]);

        return back()->with('success', "Order status updated to {$validated['status']}.");
    }
}