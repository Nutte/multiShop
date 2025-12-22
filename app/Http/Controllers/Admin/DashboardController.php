<?php
// FILE: app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index(Request $request)
    {
        $isSuperAdmin = $request->session()->get('is_super_admin', false);
        $currentTenant = $this->tenantService->getCurrentTenantId();
        
        // Если Супер-админ передал ?switch_tenant=xxx, переключаем его
        if ($isSuperAdmin && $request->has('switch_tenant')) {
            $target = $request->get('switch_tenant');
            return redirect()->to("http://{$target}." . config('app.url_base', 'trishop.local') . "/admin");
            // В реальном продакшене лучше использовать сессию или cookie для подмены контекста без редиректа на домен,
            // но редирект на домен — самый надежный способ изолировать сессии менеджеров.
        }

        // Статистика
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_amount');
        $recentOrders = Order::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'isSuperAdmin', 
            'currentTenant', 
            'totalOrders', 
            'totalRevenue', 
            'recentOrders'
        ));
    }
}