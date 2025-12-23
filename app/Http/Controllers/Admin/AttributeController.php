<?php
// FILE: app/Http/Controllers/Admin/AttributeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeOption;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    private function resolveContext(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            return $this->tenantService->getCurrentTenantId();
        }
        // Дефолт для админа
        $tenantId = $request->tenant_id ?? array_key_first(config('tenants.tenants'));
        if ($tenantId) $this->tenantService->switchTenant($tenantId);
        return $tenantId;
    }

    public function index(Request $request)
    {
        $currentTenantId = $this->resolveContext($request);
        
        $query = AttributeOption::query();
        if ($request->filled('search')) {
            $query->where('value', 'ilike', "%{$request->search}%");
        }

        $attributes = $query->get()->groupBy('type');
        
        return view('admin.attributes.index', compact('attributes', 'currentTenantId'));
    }

    public function store(Request $request)
    {
        $this->resolveContext($request);
        
        $validated = $request->validate([
            'type' => 'required|string|in:size,product_type,material',
            'value' => 'required|string|max:255',
        ]);

        AttributeOption::firstOrCreate(
            ['type' => $validated['type'], 'value' => $validated['value']],
            ['slug' => Str::slug($validated['value'])]
        );

        return back()->with('success', 'Attribute added.');
    }

    // ВАЖНО: Параметр называется $id, и маршрут должен ждать {id}
    public function destroy(Request $request, $id)
    {
        $this->resolveContext($request);
        AttributeOption::destroy($id);
        return back()->with('success', 'Attribute deleted.');
    }
}