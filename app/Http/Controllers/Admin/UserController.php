<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Services\TenantService;
use Illuminate\Http\Request;
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

    private function switchToPublic()
    {
        DB::statement('SET search_path TO public');
    }

    public function index(Request $request)
    {
        $this->switchToPublic();

        $query = User::latest();

        if (auth()->user()->role !== 'super_admin') {
            $currentTenant = $this->tenantService->getCurrentTenantId();
            $query->where(function($q) use ($currentTenant) {
                $q->where('tenant_id', $currentTenant)
                  ->orWhereNull('tenant_id');
            });
        } elseif ($request->has('tenant_id') && $request->tenant_id) {
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
        $this->switchToPublic();

        $isSuperAdmin = auth()->user()->role === 'super_admin';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string', 
            'password' => 'required|string|min:6',
            'tenant_id' => $isSuperAdmin ? 'nullable|string' : 'nullable',
        ]);

        $phone = User::normalizePhone($validated['phone']);
        
        if (User::where('phone', $phone)->exists()) {
             return back()->withErrors(['phone' => 'This phone number is already registered.'])->withInput();
        }

        $tenantId = null;
        if ($isSuperAdmin) {
            $tenantId = $request->input('tenant_id');
        } else {
            $tenantId = $this->tenantService->getCurrentTenantId();
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phone,
            'password' => $validated['password'], 
            'role' => 'client',
            'tenant_id' => $tenantId, 
            'access_key' => $validated['password'], 
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Customer created successfully.');
    }

    public function show(Request $request, $id)
    {
        // 1. Ищем пользователя в Public
        $this->switchToPublic();
        $user = User::findOrFail($id);

        // 2. Определяем список магазинов для поиска истории заказов
        $tenantsToCheck = [];
        
        if (auth()->user()->role === 'super_admin') {
            if ($request->filled('tenant_id')) {
                // Если выбран конкретный магазин, ищем только там
                $tenantsToCheck = [$request->input('tenant_id')];
            } else {
                // Иначе ищем по всем магазинам (агрегация истории)
                $tenantsToCheck = array_keys(config('tenants.tenants'));
            }
        } else {
            // Менеджер видит заказы только своего магазина
            $tenantsToCheck = [$this->tenantService->getCurrentTenantId()];
        }

        $allOrders = collect();

        // 3. Сбор заказов
        foreach ($tenantsToCheck as $tenantId) {
            if (!$tenantId) continue;

            try {
                $this->tenantService->switchTenant($tenantId);
                
                // Ищем заказы:
                // 1. Привязанные к ID пользователя (точное совпадение)
                // 2. ИЛИ по номеру телефона (если заказ был оформлен гостем до регистрации)
                $orders = Order::where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('customer_phone', $user->phone);
                })
                ->latest()
                ->get();
                
                // Добавляем информацию о магазине к каждому заказу
                $tenantName = config("tenants.tenants.{$tenantId}.name");
                foreach ($orders as $order) {
                    $order->tenant_id = $tenantId;
                    $order->tenant_name = $tenantName;
                }

                $allOrders = $allOrders->merge($orders);
                
            } catch (\Exception $e) {
                // Пропускаем магазин, если база недоступна
                continue;
            }
        }

        // 4. Сортировка: новые сверху
        $allOrders = $allOrders->sortByDesc('created_at');

        // 5. Прикрепляем коллекцию заказов к объекту пользователя
        // Теперь в шаблоне доступно $user->orders
        $user->setRelation('orders', $allOrders);

        // 6. Возвращаемся в PUBLIC схему перед рендерингом
        $this->switchToPublic();

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

        if ($request->has('generate_password')) {
            $newPassword = Str::random(8);
            $user->update([
                'password' => $newPassword,
                'access_key' => $newPassword
            ]);
            return back()->with('success', "New Key Generated: $newPassword");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string'], 
            'password' => 'nullable|string|min:6',
        ]);

        $phone = User::normalizePhone($validated['phone']);

        if (User::where('phone', $phone)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['phone' => 'This phone number is taken by another user.'])->withInput();
        }

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $phone,
        ];

        if ($request->filled('password')) {
            $data['password'] = $validated['password'];
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