<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\TenantService;

class CheckOrderTenantAccess
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        // Супер-админ имеет доступ ко всем заказам
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Для обычных админов проверяем принадлежность заказа к текущему тенанту
        $orderId = $request->route('id');
        if ($orderId) {
            $currentTenantId = $this->tenantService->getCurrentTenantId();
            
            if (!$currentTenantId) {
                abort(403, 'Tenant context is not set.');
            }
            
            $this->tenantService->switchTenant($currentTenantId);
            
            $orderExists = Order::where('id', $orderId)->exists();
            
            if (!$orderExists) {
                abort(403, 'You do not have access to this order.');
            }
        }

        return $next($request);
    }
}