<?php
// FILE: app/Http/Controllers/Admin/ProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\AttributeOption;
use App\Services\ProductService;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    protected TenantService $tenantService;
    protected ProductService $productService;

    public function __construct(TenantService $tenantService, ProductService $productService)
    {
        $this->tenantService = $tenantService;
        $this->productService = $productService;
    }

    private function resolveContext(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            return $this->tenantService->getCurrentTenantId();
        }
        $tenantId = $request->get('tenant_id');
        if ($tenantId) {
            $this->tenantService->switchTenant($tenantId);
            return $tenantId;
        }
        $current = $this->tenantService->getCurrentTenantId();
        if ($current) return $current;
        return null;
    }

    private function getAllTenantsProducts(Request $request)
    {
        $allProducts = new Collection();
        foreach (config('tenants.tenants') as $id => $config) {
            try {
                $this->tenantService->switchTenant($id);
                $query = Product::with('categories')->latest()->take(5);
                if ($request->filled('search')) {
                    $s = $request->search;
                    $query->where(fn($q) => $q->where('name', 'ilike', "%$s%")->orWhere('sku', 'ilike', "%$s%"));
                }
                $products = $query->get();
                $products->each(function($p) use ($config, $id) {
                    $p->tenant_name = $config['name'];
                    $p->tenant_id = $id;
                    $p->preview_url = "http://{$config['domain']}/products/{$p->slug}?preview=true";
                });
                $allProducts = $allProducts->merge($products);
            } catch (\Exception $e) { continue; }
        }
        return $allProducts;
    }

    public function index(Request $request)
    {
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $selectedTenant = $request->get('tenant_id');
        
        if ($isSuperAdmin && empty($selectedTenant)) {
            $products = $this->getAllTenantsProducts($request);
            $categories = new Collection(); $sizes = new Collection(); $types = new Collection();
            $currentTenantId = null;
            return view('admin.products.index', compact('products', 'categories', 'sizes', 'types', 'currentTenantId'));
        }

        $currentTenantId = $this->resolveContext($request);
        if (!$currentTenantId && $isSuperAdmin) {
             $first = array_key_first(config('tenants.tenants'));
             $this->tenantService->switchTenant($first);
             $currentTenantId = $first;
        }

        $query = Product::with('categories');
        if ($request->filled('search')) {
            $query->where(fn($q) => $q->where('name', 'ilike', "%{$request->search}%")->orWhere('sku', 'ilike', "%{$request->search}%"));
        }
        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->category_id));
        }
        if ($request->filled('size')) {
            $query->whereRaw("attributes->'size' ? ?", [$request->size]);
        }
        if ($request->filled('type')) {
            $query->whereRaw("attributes->>'type' = ?", [$request->type]);
        }

        $products = $query->latest()->paginate(20)->withQueryString();
        $tenantDomain = config('tenants.tenants.' . $currentTenantId . '.domain');
        foreach ($products as $product) {
            $product->preview_url = "http://{$tenantDomain}/products/{$product->slug}?preview=true";
            $product->tenant_name = config("tenants.tenants.{$currentTenantId}.name");
            $product->tenant_id = $currentTenantId;
        }

        $categories = Category::orderBy('name')->get();
        $sizes = AttributeOption::where('type', 'size')->orderBy('value')->get();
        $types = AttributeOption::where('type', 'product_type')->orderBy('value')->get();

        return view('admin.products.index', compact('products', 'categories', 'sizes', 'types', 'currentTenantId'));
    }

    public function form(Request $request, $id = null)
    {
        $currentTenantId = $this->resolveContext($request);
        if (!$currentTenantId && auth()->user()->role === 'super_admin') {
             $first = array_key_first(config('tenants.tenants'));
             $this->tenantService->switchTenant($first);
             $currentTenantId = $first;
        }

        if ($id) {
            $product = Product::with('categories')->findOrFail($id);
            $action = route('admin.products.update', $product->id);
            $method = 'PUT';
            $title = "Edit: " . $product->name;
            $tenantDomain = config('tenants.tenants.' . $currentTenantId . '.domain');
            $previewUrl = "http://{$tenantDomain}/products/{$product->slug}?preview=true";
        } else {
            $product = new Product();
            $action = route('admin.products.store');
            $method = 'POST';
            $title = "Add New Product";
            $previewUrl = null;
        }

        $categories = Category::all();
        $sizes = AttributeOption::where('type', 'size')->get();
        $types = AttributeOption::where('type', 'product_type')->get();

        return view('admin.products.form', compact(
            'product', 'action', 'method', 'title', 'categories', 'sizes', 'types', 'currentTenantId', 'previewUrl'
        ));
    }

    public function create(Request $request) { return $this->form($request); }
    public function edit(Request $request, $id) { return $this->form($request, $id); }

    public function store(Request $request)
    {
        if (auth()->user()->role === 'super_admin' && $request->has('target_tenant')) {
            $this->tenantService->switchTenant($request->target_tenant);
        } else {
            $this->resolveContext($request);
        }

        $validated = $this->validateProduct($request);
        $this->syncAttributes($request);
        
        $imagePath = $request->hasFile('image') ? $request->file('image')->store('media', 'tenant') : null;

        $product = $this->productService->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
            'price' => $validated['price'],
            'sku' => $validated['sku'],
            'stock_quantity' => $validated['stock_quantity'],
            'description' => $request->input('description'),
            'image_path' => $imagePath,
            'attributes' => [
                'type' => $request->attributes_type,
                'size' => $request->attributes_size ?? [],
            ]
        ]);

        $this->syncCategories($product, $request->categories);

        return redirect()->route('admin.products.index', ['tenant_id' => $this->tenantService->getCurrentTenantId()])
                         ->with('success', 'Product created successfully.');
    }

    public function update(Request $request, $id)
    {
        $this->resolveContext($request);
        $product = Product::findOrFail($id);
        
        $validated = $this->validateProduct($request, $id);
        $this->syncAttributes($request);

        if ($request->hasFile('image')) {
            if ($product->image_path) Storage::disk('tenant')->delete($product->image_path);
            $product->image_path = $request->file('image')->store('media', 'tenant');
        }

        $product->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'sku' => $validated['sku'],
            'stock_quantity' => $validated['stock_quantity'],
            'description' => $request->input('description'),
            'attributes' => [
                'type' => $request->attributes_type,
                'size' => $request->attributes_size ?? [],
            ]
        ]);

        $this->syncCategories($product, $request->categories);

        return redirect()->route('admin.products.index', ['tenant_id' => $this->tenantService->getCurrentTenantId()])
                         ->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $this->resolveContext($request);
        $product = Product::findOrFail($id);
        if ($product->image_path) Storage::disk('tenant')->delete($product->image_path);
        $product->delete();
        return back()->with('success', 'Product deleted.');
    }

    private function validateProduct(Request $request, $id = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'categories' => 'required|array',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'required|string|max:50',
            'image' => 'nullable|image|max:2048',
            'attributes_type' => 'required|string', 
            'attributes_size' => 'nullable|array', 
        ]);
    }

    private function syncAttributes(Request $request)
    {
        // ИСПРАВЛЕНИЕ: Ищем по слагу, чтобы избежать Unique Violation
        $typeSlug = Str::slug($request->attributes_type);
        AttributeOption::firstOrCreate(
            ['type' => 'product_type', 'slug' => $typeSlug], 
            ['value' => $request->attributes_type]
        );

        foreach ($request->attributes_size ?? [] as $sizeName) {
            $sizeSlug = Str::slug($sizeName);
            AttributeOption::firstOrCreate(
                ['type' => 'size', 'slug' => $sizeSlug], 
                ['value' => $sizeName]
            );
        }
    }

    private function syncCategories(Product $product, array $categoriesInput)
    {
        $categoryIds = [];
        foreach ($categoriesInput as $input) {
            if (is_numeric($input)) {
                $categoryIds[] = $input;
            } else {
                // ИСПРАВЛЕНИЕ: Ищем по слагу! 
                // Если ввести "TestCat", слаг будет "testcat".
                // Если "testcat" уже есть, firstOrCreate его найдет и вернет ID.
                $slug = Str::slug($input);
                $newCat = Category::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $input]
                );
                $categoryIds[] = $newCat->id;
            }
        }
        $product->categories()->sync($categoryIds);
    }
}