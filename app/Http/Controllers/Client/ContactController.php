<?php

// FILE: app/Http/Controllers/Client/ContactController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
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
        $view = "tenants.{$tenantId}.contact";
        if (!view()->exists($view)) {
            $view = 'shop.contact';
        }
        return view($view);
    }

    public function store(Request $request)
    {
        $this->resolveTenant();

        $validated = $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|min:10|max:2000',
        ]);

        // Если пользователь авторизован, добавляем его ID
        if (Auth::check()) {
            $validated['user_id'] = Auth::id();
            // Если телефон не введен в форме, но есть в профиле - можно дополнить (опционально)
            if (empty($validated['phone'])) {
                $validated['phone'] = Auth::user()->phone;
            }
        }

        ContactMessage::create($validated);

        return back()->with('success', 'Message sent successfully. Support team will contact you.');
    }
}