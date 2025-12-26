<?php
// FILE: app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    // Вспомогательный метод для определения контекста (как в OrderController)
    private function resolveContext(Request $request)
    {
        if (auth()->user()->role === 'super_admin') {
            $tenantId = $request->get('tenant_id');
            // Если tenant_id передан, переключаемся. Если нет — остаемся в текущем или дефолтном.
            if ($tenantId) {
                $this->tenantService->switchTenant($tenantId);
                return $tenantId;
            }
        }
        return $this->tenantService->getCurrentTenantId();
    }

    // 1. СПИСОК КЛИЕНТОВ
    public function index(Request $request)
    {
        $currentTenantId = $this->resolveContext($request);
        $isSuperAdmin = auth()->user()->role === 'super_admin';

        // Фильтрация
        $query = User::where('role', 'client')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);

        return view('admin.users.index', compact('users', 'currentTenantId'));
    }

    // 2. ПРОФИЛЬ КЛИЕНТА (С историей заказов)
    public function show(Request $request, $id)
    {
        $this->resolveContext($request);

        $user = User::with(['orders' => function($q) {
            $q->latest();
        }])->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    // 3. СМЕНА ПАРОЛЯ (Админом)
    public function update(Request $request, $id)
    {
        $this->resolveContext($request);
        $user = User::findOrFail($id);

        // Если админ нажал "Сгенерировать новый пароль"
        if ($request->has('generate_password')) {
            $newPassword = Str::random(8); // Простой 8-значный пароль
            
            $user->update([
                'password' => Hash::make($newPassword),
                'access_key' => $newPassword // Обновляем и ключ доступа, чтобы он работал
            ]);

            return back()->with('success', "Password reset successfully. New Key: {$newPassword}");
        }

        return back();
    }
}