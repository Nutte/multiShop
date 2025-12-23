<?php
// FILE: app/Http/Controllers/Admin/ClothingLineController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClothingLine;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClothingLineController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    // Хелпер контекста (аналогично другим контроллерам)
    private function resolveContext(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            return $this->tenantService->getCurrentTenantId();
        }

        // Дефолт или выбранный магазин
        $tenantId = $request->tenant_id ?? array_key_first(config('tenants.tenants'));
        
        if ($tenantId) {
            $this->tenantService->switchTenant($tenantId);
        }
        
        return $tenantId;
    }

    public function index(Request $request)
    {
        $currentTenantId = $this->resolveContext($request);
        
        $query = ClothingLine::withCount('products');
        
        if ($request->filled('search')) {
            $query->where('name', 'ilike', "%{$request->search}%");
        }

        $lines = $query->orderBy('name')->get();

        return view('admin.clothing_lines.index', compact('lines', 'currentTenantId'));
    }

    public function store(Request $request)
    {
        $this->resolveContext($request);
        
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $slug = Str::slug($validated['name']);

        ClothingLine::firstOrCreate(
            ['slug' => $slug],
            ['name' => $validated['name']]
        );

        return back()->with('success', 'Collection created/found.');
    }

    public function update(Request $request, $id)
    {
        $this->resolveContext($request);
        $line = ClothingLine::findOrFail($id);
        
        $validated = $request->validate(['name' => 'required|string|max:255']);
        
        try {
            $line->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name'])
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Collection with this name already exists.');
        }
        
        return back()->with('success', 'Collection updated.');
    }

    public function destroy(Request $request, $id)
    {
        $this->resolveContext($request);
        ClothingLine::destroy($id);
        return back()->with('success', 'Collection deleted.');
    }
}