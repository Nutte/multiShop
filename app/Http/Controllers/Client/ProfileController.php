<?php
// FILE: app/Http/Controllers/Client/ProfileController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class ProfileController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    private function resolveTenant()
    {
        $host = request()->getHost();
        $map = $this->tenantService->getDomainMap();
        $tenantId = $map[$host] ?? 'default';
        $this->tenantService->switchTenant($tenantId);
        return $tenantId;
    }

    public function index()
    {
        $tenantId = $this->resolveTenant();
        $user = Auth::user();
        
        $orders = $user->orders()->with('items')->get();

        $view = "tenants.{$tenantId}.profile.index";
        if (!view()->exists($view)) $view = 'client.profile.index';

        return view($view, compact('orders', 'user'));
    }

    public function showOrder($id)
    {
        $tenantId = $this->resolveTenant();
        
        $order = Order::with('items')->where('user_id', Auth::id())->findOrFail($id);
        
        // Получаем сгенерированный пароль из сессии (если это только что созданный заказ)
        $generatedPassword = session('generated_password');

        $view = "tenants.{$tenantId}.profile.order";
        if (!view()->exists($view)) $view = 'client.profile.order';

        return view($view, compact('order', 'generatedPassword'));
    }
}