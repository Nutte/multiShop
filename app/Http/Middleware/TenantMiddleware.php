<?php
// FILE: app/Http/Middleware/TenantMiddleware.php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            // Принудительно ставим public по умолчанию для авторизации
            DB::statement("SET search_path TO public");

            // Если Супер-Админ выбрал магазин для просмотра
            if (session()->has('admin_current_tenant_id') && !$request->routeIs('admin.login')) {
                try {
                    $targetTenant = session('admin_current_tenant_id');
                    $this->tenantService->switchTenant($targetTenant);
                } catch (\Exception $e) {
                    Log::warning("Super admin tried to switch to invalid tenant: {$targetTenant}", ['error' => $e->getMessage()]);
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
                Log::error("Tenant switch failed for host: {$host}", [
                    'tenant_id' => $tenantId,
                    'error' => $e->getMessage()
                ]);
                abort(500, "Tenant switch failed: " . $e->getMessage());
            }

            return $next($request);
        }

        Log::warning("Site not found for domain: {$host}");
        abort(404, "Site not found for domain: {$host}");
    }
}