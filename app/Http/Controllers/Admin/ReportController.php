<?php
// FILE: app/Http/Controllers/Admin/ReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TenantService;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index()
    {
        $user = auth()->user();
        $reportData = [];
        $grandTotal = 0;

        // ЛОГИКА СУПЕР-АДМИНА: Пробегаем по всем схемам
        if ($user->role === 'super_admin') {
            $tenants = config('tenants.tenants');
            
            foreach ($tenants as $id => $config) {
                // Переключаем контекст БД
                // Важно: try-catch, чтобы ошибка в одной схеме не положила весь отчет
                try {
                    $this->tenantService->switchTenant($id);
                    
                    $total = Order::sum('total_amount');
                    $count = Order::count();
                    $avg = $count > 0 ? $total / $count : 0;

                    $reportData[] = [
                        'tenant' => $config['name'],
                        'orders_count' => $count,
                        'total_revenue' => $total,
                        'average_check' => $avg,
                    ];
                    $grandTotal += $total;

                } catch (\Exception $e) {
                    $reportData[] = [
                        'tenant' => $config['name'] . ' (Error)',
                        'orders_count' => 0,
                        'total_revenue' => 0,
                        'average_check' => 0,
                    ];
                }
            }
            
            // Возвращаемся в Public (или в контекст админки по умолчанию)
            DB::statement("SET search_path TO public");

        } else {
            // ЛОГИКА МЕНЕДЖЕРА: Данные только текущей схемы
            // Middleware уже переключил нас в нужную схему при входе (если мы в режиме магазина)
            // Но так как мы в центральной админке, нам нужно убедиться, что контекст верен.
            
            if ($user->tenant_id) {
                $this->tenantService->switchTenant($user->tenant_id);
                
                $total = Order::sum('total_amount');
                $count = Order::count();
                $avg = $count > 0 ? $total / $count : 0;

                $reportData[] = [
                    'tenant' => ucfirst($user->tenant_id),
                    'orders_count' => $count,
                    'total_revenue' => $total,
                    'average_check' => $avg,
                ];
                $grandTotal = $total;
            }
        }

        return view('admin.reports.index', compact('reportData', 'grandTotal'));
    }
}