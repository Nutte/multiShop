<?php
// FILE: app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->role === 'super_admin';
        
        $currentTenantId = null;

        if ($isSuperAdmin) {
            if ($request->has('tenant_id')) {
                $currentTenantId = $request->tenant_id;
            } elseif (session()->has('admin_current_tenant_id')) {
                $currentTenantId = session('admin_current_tenant_id');
            }
            if ($currentTenantId === 'root') $currentTenantId = null;
        } else {
            $currentTenantId = $this->tenantService->getCurrentTenantId();
        }

        $stats = [
            'total_orders' => 0,
            'total_revenue' => 0,
            'recent_orders' => new Collection(),
            'tenant_name' => $currentTenantId ? config("tenants.tenants.$currentTenantId.name") : 'All Shops (Global Overview)'
        ];

        if ($currentTenantId) {
            // --- РЕЖИМ ОДНОГО МАГАЗИНА ---
            $this->tenantService->switchTenant($currentTenantId);
            
            $stats['total_orders'] = Order::count();
            $stats['total_revenue'] = Order::sum('total_amount');
            
            $recent = Order::latest()->take(5)->get();
            $storeName = config("tenants.tenants.$currentTenantId.name");
            $recent->each(fn($o) => $o->store_name = $storeName);
            
            $stats['recent_orders'] = $recent;

        } elseif ($isSuperAdmin) {
            // --- ГЛОБАЛЬНЫЙ РЕЖИМ ---
            $allOrders = []; // Используем простой массив, чтобы избежать конфликта ID

            foreach (config('tenants.tenants') as $id => $config) {
                try {
                    $this->tenantService->switchTenant($id);
                    
                    $stats['total_orders'] += Order::count();
                    $stats['total_revenue'] += Order::sum('total_amount');
                    
                    // Берем последние 5 с каждого магазина
                    $orders = Order::latest()->take(5)->get();
                    
                    foreach ($orders as $order) {
                        $order->store_name = $config['name'];
                        $order->tenant_id = $id;
                        $allOrders[] = $order; // Просто добавляем в массив
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // Превращаем в коллекцию, сортируем по дате и берем топ-5
            $stats['recent_orders'] = collect($allOrders)->sortByDesc('created_at')->take(5);
        }

        return view('admin.dashboard', compact('stats', 'currentTenantId'));
    }
}