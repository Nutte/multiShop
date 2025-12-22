<?php
// FILE: app/Http/Middleware/TenantMiddleware.php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Добавлено
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $adminDomain = config('tenants.admin_domain');

        // РЕЖИМ 1: АДМИНКА
        if ($host === $adminDomain) {
            // ИСПРАВЛЕНИЕ: Принудительно ставим public по умолчанию
            // Это гарантирует, что Auth будет искать юзера в public.users
            DB::statement("SET search_path TO public");

            // Если Супер-Админ выбрал магазин "посмотреть", переключаем ТОЛЬКО для данных
            // Но это должно быть аккуратно реализовано в контроллерах. 
            // Для входа (Login) критически важно быть в public.
            
            if (session()->has('admin_current_tenant_id')) {
                try {
                    $targetTenant = session('admin_current_tenant_id');
                    // Важно: switchTenant меняет search_path.
                    // Для корректной работы админки нам нужно:
                    // 1. Auth -> Public schema
                    // 2. Orders -> Tenant schema
                    // Это сложно. Упростим: если мы логинимся (маршрут login), мы форсируем public.
                    
                    if (!$request->routeIs('admin.login')) {
                         $this->tenantService->switchTenant($targetTenant);
                    }
                } catch (\Exception $e) {
                    session()->forget('admin_current_tenant_id');
                }
            }
            
            return $next($request);
        }

        // РЕЖИМ 2: МАГАЗИНЫ
        $domainMap = $this->tenantService->getDomainMap();

        if (array_key_exists($host, $domainMap)) {
            $tenantId = $domainMap[$host];
            try {
                $this->tenantService->switchTenant($tenantId);
            } catch (\Exception $e) {
                abort(500, "Tenant switch failed: " . $e->getMessage());
            }

            return $next($request);
        }

        abort(404, "Site not found for domain: {$host}");
    }
}