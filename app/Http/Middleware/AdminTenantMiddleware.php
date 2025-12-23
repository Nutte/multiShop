<?php
// FILE: app/Http/Middleware/AdminTenantMiddleware.php

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminTenantMiddleware
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Работаем только если пользователь авторизован
        if ($user) {
            
            // СЦЕНАРИЙ 1: Менеджер
            // У него жесткая привязка к магазину. Переключаем принудительно.
            if ($user->role === 'manager' && $user->tenant_id) {
                try {
                    $this->tenantService->switchTenant($user->tenant_id);
                } catch (\Exception $e) {
                    abort(500, "Manager assigned to invalid tenant: " . $user->tenant_id);
                }
            }
            
            // СЦЕНАРИЙ 2: Супер-Админ
            // Он может переключаться через сессию.
            // Проверяем сессию и переключаем, если выбрано.
            elseif ($user->role === 'super_admin') {
                if (session()->has('admin_current_tenant_id')) {
                    try {
                        $this->tenantService->switchTenant(session('admin_current_tenant_id'));
                    } catch (\Exception $e) {
                        session()->forget('admin_current_tenant_id');
                    }
                }
            }
        }

        return $next($request);
    }
}