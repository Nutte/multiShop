<?php
// FILE: app/Http/Controllers/Client/AuthController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\TenantService;
use App\Services\CartService; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Order;

class AuthController extends Controller
{
    protected TenantService $tenantService;
    protected CartService $cartService;

    public function __construct(TenantService $tenantService, CartService $cartService)
    {
        $this->tenantService = $tenantService;
        $this->cartService = $cartService;
    }

    private function resolveTenant()
    {
        $host = request()->getHost();
        $map = $this->tenantService->getDomainMap();
        $tenantId = $map[$host] ?? 'default';
        $this->tenantService->switchTenant($tenantId);
        return $tenantId;
    }

    public function showLogin()
    {
        $tenantId = $this->resolveTenant();
        $view = "tenants.{$tenantId}.auth.login";
        if (!view()->exists($view)) {
            $view = 'client.auth.login';
        }
        
        return view($view);
    }

    public function login(Request $request)
    {
        $tenantId = $this->resolveTenant();

        $dirtyPhone = $request->input('phone');
        $normalizedPhone = $dirtyPhone ? User::normalizePhone($dirtyPhone) : null;

        if ($normalizedPhone) {
            $request->merge(['phone' => $normalizedPhone]);
        }

        $credentials = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $guestSessionId = Session::getId();

        // ИСПРАВЛЕНИЕ: Не проверяем 'tenant_id' в attempt.
        // Это позволяет войти пользователям, у которых tenant_id = null (глобальные)
        // или созданным админом без привязки к магазину.
        $authCredentials = [
            'phone' => $credentials['phone'],
            'password' => $credentials['password'],
        ];

        if (Auth::attempt($authCredentials)) {
            $request->session()->regenerate();
            
            /** @var User $user */
            $user = Auth::user();

            // Ручная проверка: Если пользователь жестко привязан к другому магазину -> выход
            if ($user->tenant_id && $user->tenant_id !== $tenantId) {
                Auth::logout();
                return back()->withErrors([
                    'phone' => 'You are not registered in this store.',
                ]);
            }

            // Связывание сессии и заказов
            $newSessionId = Session::getId();
            if ($guestSessionId !== $newSessionId) {
                $this->cartService->migrateSessionCart($guestSessionId, $newSessionId);
            }

            Order::where('customer_phone', $user->phone)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]);

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'phone' => 'The provided credentials do not match our records.',
        ]);
    }

    public function register(Request $request)
    {
        $tenantId = $this->resolveTenant();

        $dirtyPhone = $request->input('phone');
        if ($dirtyPhone) {
            $request->merge(['phone' => User::normalizePhone($dirtyPhone)]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                // Проверяем уникальность для текущего магазина или глобально
                Rule::unique('users')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
                }),
            ],
            'password' => 'required|string|min:6|confirmed',
        ]);

        // ИСПРАВЛЕНИЕ: Не хешируем пароль вручную (cast в модели)
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone, 
            'password' => $request->password, 
            'tenant_id' => $tenantId,
            'role' => 'client', 
        ]);

        Auth::login($user);
        
        Order::where('customer_phone', $user->phone)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);

        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function updatePassword(Request $request)
    {
        $this->resolveTenant();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        $isPasswordCorrect = Hash::check($request->current_password, $user->password);
        $isAccessKeyCorrect = ($user->access_key && $request->current_password === $user->access_key);

        if (!$isPasswordCorrect && !$isAccessKeyCorrect) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        /** @var \App\Models\User $user */
        // ИСПРАВЛЕНИЕ: Не хешируем пароль вручную
        $user->update([
            'password' => $request->new_password, 
            'access_key' => $request->new_password 
        ]);

        return back()->with('success', 'Password updated successfully!');
    }
}