<?php
// FILE: app/Http/Controllers/Admin/CategoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
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
        
        $tenantId = $request->tenant_id ?? array_key_first(config('tenants.tenants'));
        if ($tenantId) {
            $this->tenantService->switchTenant($tenantId);
        }
        return $tenantId;
    }

    public function index(Request $request)
    {
        $currentTenantId = $this->resolveContext($request);
        
        $query = Category::withCount('products');
        
        if ($request->filled('search')) {
            $query->where('name', 'ilike', "%{$request->search}%");
        }

        $categories = $query->orderBy('name')->get();

        return view('admin.categories.index', compact('categories', 'currentTenantId'));
    }

    public function store(Request $request)
    {
        $this->resolveContext($request);
        
        $validated = $request->validate(['name' => 'required|string|max:255']);

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return back()->with('success', 'Category created.');
    }

    public function destroy(Request $request, $id)
    {
        $this->resolveContext($request);
        Category::destroy($id);
        return back()->with('success', 'Category deleted.');
    }
}