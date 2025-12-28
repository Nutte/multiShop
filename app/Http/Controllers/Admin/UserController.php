<?php
// FILE: app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    // Хелпер для нормализации телефона (Украина)
    private function normalizePhone($phone)
    {
        // Удаляем все кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Если начинается с 380... -> +380...
        if (str_starts_with($phone, '380')) {
            return '+' . $phone;
        }
        // Если начинается с 0... (097...) -> +380...
        if (str_starts_with($phone, '0')) {
            return '+38' . $phone;
        }
        
        // В остальных случаях возвращаем как есть (или добавляем +)
        return '+' . $phone;
    }

    private function switchToPublic()
    {
        DB::statement('SET search_path TO public');
    }

    public function index(Request $request)
    {
        // 1. Принудительно ищем пользователей в PUBLIC схеме
        $this->switchToPublic();

        $query = User::where('role', 'client')->latest();

        // 2. Логика фильтрации
        if (auth()->user()->role !== 'super_admin') {
            // Менеджер видит своих + глобальных
            $currentTenant = $this->tenantService->getCurrentTenantId();
            $query->where(function($q) use ($currentTenant) {
                $q->where('tenant_id', $currentTenant)
                  ->orWhereNull('tenant_id');
            });
        } elseif ($request->has('tenant_id') && $request->tenant_id) {
            // Супер-админ фильтрует по магазину
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);
        
        // Передаем текущий tenant_id для сохранения контекста в ссылках (хотя для users это менее критично)
        $currentTenantId = auth()->user()->role === 'super_admin' ? $request->get('tenant_id') : $this->tenantService->getCurrentTenantId();

        return view('admin.users.index', compact('users', 'currentTenantId'));
    }

    public function create()
    {
        $user = new User();
        $tenants = config('tenants.tenants');
        return view('admin.users.form', compact('user', 'tenants'));
    }

    public function store(Request $request)
    {
        $this->switchToPublic(); // Пишем в public

        $isSuperAdmin = auth()->user()->role === 'super_admin';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'tenant_id' => $isSuperAdmin ? 'nullable|string' : 'nullable',
        ]);

        // Нормализация телефона
        $phone = $this->normalizePhone($validated['phone']);
        
        // Проверка уникальности телефона ПОСЛЕ нормализации
        if (User::where('phone', $phone)->exists()) {
             return back()->withErrors(['phone' => 'This phone number is already registered.'])->withInput();
        }

        // ОПРЕДЕЛЕНИЕ МАГАЗИНА
        $tenantId = null;
        if ($isSuperAdmin) {
            $tenantId = $request->input('tenant_id');
        } else {
            // Если менеджер создает пользователя - он ПРИВЯЗЫВАЕТСЯ к магазину менеджера
            $tenantId = $this->tenantService->getCurrentTenantId();
        }

        $accessKey = $validated['password']; 

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phone,
            'password' => Hash::make($validated['password']),
            'role' => 'client',
            'tenant_id' => $tenantId, // Теперь менеджер записывает свой ID
            'access_key' => $accessKey,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Customer created successfully.');
    }

    // ИСПОЛЬЗУЕМ $id ВМЕСТО Model Binding, ЧТОБЫ ИЗБЕЖАТЬ 404 В ЧУЖОЙ СХЕМЕ
    public function show($id)
    {
        $this->switchToPublic();
        $user = User::findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    public function edit($id)
    {
        $this->switchToPublic();
        $user = User::findOrFail($id);
        $tenants = config('tenants.tenants');
        return view('admin.users.form', compact('user', 'tenants'));
    }

    public function update(Request $request, $id)
    {
        $this->switchToPublic();
        $user = User::findOrFail($id);

        // Генерация пароля (кнопка Reset)
        if ($request->has('generate_password')) {
            $newPassword = Str::random(8);
            $user->update([
                'password' => Hash::make($newPassword),
                'access_key' => $newPassword
            ]);
            return back()->with('success', "New Key Generated: $newPassword");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string'], // Убрали уникальность здесь, проверим вручную
            'password' => 'nullable|string|min:6',
        ]);

        $phone = $this->normalizePhone($validated['phone']);

        // Ручная проверка уникальности телефона при обновлении
        if (User::where('phone', $phone)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['phone' => 'This phone number is taken by another user.'])->withInput();
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phone,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
            $data['access_key'] = $validated['password'];
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Customer profile updated.');
    }
    
    public function destroy($id)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403);
        }
        $this->switchToPublic();
        User::destroy($id);
        return back()->with('success', 'User deleted.');
    }
}