<?php
// FILE: app/Http/Controllers/Client/AuthController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
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

    public function showLogin()
    {
        $tenantId = $this->resolveTenant();
        // Уникальный шаблон для каждого магазина или общий
        $view = "tenants.{$tenantId}.auth.login";
        if (!view()->exists($view)) {
            $view = 'client.auth.login'; // Фолбэк на общий
        }
        
        return view($view);
    }

    public function login(Request $request)
    {
        $this->resolveTenant();

        $credentials = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        // 1. Стандартная попытка входа (Пароль)
        if (Auth::attempt(['phone' => $credentials['phone'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return redirect()->route('client.profile');
        }

        // 2. Попытка входа через Access Key (Резервный вход)
        $user = User::where('phone', $credentials['phone'])->first();
        
        if ($user && $user->access_key) {
            // Сравниваем введенный пароль с расшифрованным ключом
            if ($credentials['password'] === $user->access_key) {
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->route('client.profile')->with('success', 'Logged in via Access Key');
            }
        }

        return back()->withErrors([
            'phone' => 'The provided credentials do not match our records.',
        ]);
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

        // Проверяем старый пароль (или access_key)
        $isPasswordCorrect = Hash::check($request->current_password, $user->password);
        $isAccessKeyCorrect = ($user->access_key && $request->current_password === $user->access_key);

        if (!$isPasswordCorrect && !$isAccessKeyCorrect) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        /** @var \App\Models\User $user */
        $user->update([
            'password' => Hash::make($request->new_password),
            // ВАЖНО: Обновляем Access Key тоже, чтобы на чеке всегда был АКТУАЛЬНЫЙ пароль
            // Laravel автоматически зашифрует это поле благодаря $casts в модели
            'access_key' => $request->new_password 
        ]);

        return back()->with('success', 'Password updated successfully! Your receipt credentials are also updated.');
    }
}