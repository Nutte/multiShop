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

    public function index()
    {
        // Проверка контекста для супер-админа
        if (auth()->user()->role === 'super_admin' && !$this->tenantService->getCurrentTenantId()) {
            return view('admin.products.select_tenant'); // Используем тот же вью выбора
        }

        $categories = Category::withCount('products')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function destroy($id)
    {
        Category::destroy($id);
        return back()->with('success', 'Category deleted.');
    }
}