<?php
// FILE: app/Http/Controllers/Admin/ProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\AttributeOption;
use App\Models\ClothingLine;
use App\Services\ProductService;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB; // Добавлено для транзакций

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

    // --- ACTIONS ---

    public function index(Request $request)
    {
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $selectedTenant = $request->get('tenant_id');
        
        // Режим "ALL STORES"
        if ($isSuperAdmin && empty($selectedTenant)) {
            $products = $this->getAllTenantsProducts($request);
            $categories = new Collection(); 
            $sizes = new Collection(); 
            $types = new Collection();
            $lines = new Collection();
            $currentTenantId = null;
            return view('admin.products.index', compact('products', 'categories', 'sizes', 'types', 'lines', 'currentTenantId'));
        }

        // Режим "SINGLE STORE"
        $currentTenantId = $this->resolveContext($request);
        if (!$currentTenantId && $isSuperAdmin) {
             $first = array_key_first(config('tenants.tenants'));
             $this->tenantService->switchTenant($first);
             $currentTenantId = $first;
        }

        $query = Product::with(['categories', 'images', 'variants', 'clothingLine']);

        // Фильтры
        if ($request->filled('search')) {
            $query->where(fn($q) => $q->where('name', 'ilike', "%{$request->search}%")->orWhere('sku', 'ilike', "%{$request->search}%"));
        }
        if ($request->filled('category_id')) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->category_id));
        }
        if ($request->filled('clothing_line_id')) {
            $query->where('clothing_line_id', $request->clothing_line_id);
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
        $lines = ClothingLine::orderBy('name')->get(); 
        $sizes = AttributeOption::where('type', 'size')->orderBy('value')->get();
        $types = AttributeOption::where('type', 'product_type')->orderBy('value')->get();

        return view('admin.products.index', compact('products', 'categories', 'sizes', 'types', 'lines', 'currentTenantId'));
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
            $product = Product::with(['categories', 'images', 'variants', 'clothingLine'])->findOrFail($id);
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
        $lines = ClothingLine::all(); 
        $sizes = AttributeOption::where('type', 'size')->get();
        $types = AttributeOption::where('type', 'product_type')->get();

        return view('admin.products.form', compact(
            'product', 'action', 'method', 'title', 'categories', 'lines', 'sizes', 'types', 'currentTenantId', 'previewUrl'
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

        // Используем транзакцию для атомарности
        DB::transaction(function () use ($request, $validated) {
            
            // 1. Считаем сток
            $variantsInput = $request->input('variants', []);
            $totalStock = 0;
            $sizeList = [];
            foreach ($variantsInput as $v) {
                if (!empty($v['size'])) {
                    $totalStock += (int)($v['stock'] ?? 0);
                    $sizeList[] = $v['size'];
                }
            }

            // 2. Линейка
            $lineId = $this->resolveClothingLineId($request->clothing_line);

            // 3. Продукт
            $product = $this->productService->create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
                'price' => $validated['price'],
                'sku' => $validated['sku'],
                'stock_quantity' => $totalStock,
                'description' => $request->input('description'),
                'clothing_line_id' => $lineId,
                'attributes' => [
                    'type' => $request->attributes_type,
                    'size' => $sizeList,
                ]
            ]);

            // 4. Синхронизация
            $this->syncAttributesData($request->attributes_type, $sizeList);
            $this->syncCategories($product, $request->categories);
            $this->syncImages($request, $product);
            $this->syncVariants($product, $variantsInput);
        });

        return redirect()->route('admin.products.index', ['tenant_id' => $this->tenantService->getCurrentTenantId()])
                         ->with('success', 'Product created successfully.');
    }

    public function update(Request $request, $id)
    {
        $this->resolveContext($request);
        $product = Product::findOrFail($id);
        
        $validated = $this->validateProduct($request, $id);
        
        DB::transaction(function () use ($request, $product, $validated) {
            
            $variantsInput = $request->input('variants', []);
            $totalStock = 0;
            $sizeList = [];
            foreach ($variantsInput as $v) {
                if (!empty($v['size'])) {
                    $totalStock += (int)($v['stock'] ?? 0);
                    $sizeList[] = $v['size'];
                }
            }

            $lineId = $this->resolveClothingLineId($request->clothing_line);

            $product->update([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'sku' => $validated['sku'],
                'stock_quantity' => $totalStock,
                'description' => $request->input('description'),
                'clothing_line_id' => $lineId,
                'attributes' => [
                    'type' => $request->attributes_type,
                    'size' => $sizeList,
                ]
            ]);

            $this->syncAttributesData($request->attributes_type, $sizeList);
            $this->syncCategories($product, $request->categories);
            $this->syncImagesUpdate($request, $product);
            $this->syncVariants($product, $variantsInput);
        });

        return redirect()->route('admin.products.index', ['tenant_id' => $this->tenantService->getCurrentTenantId()])
                         ->with('success', 'Product updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $this->resolveContext($request);
        $product = Product::findOrFail($id);
        $this->productService->delete($product);
        return back()->with('success', 'Product deleted.');
    }

    // --- PRIVATE HELPERS ---

    private function validateProduct(Request $request, $id = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'sku' => 'required|string|max:50',
            'categories' => 'required|array',
            'attributes_type' => 'required|string',
            'clothing_line' => 'nullable|string|max:255',
            'variants.*.size' => 'required|string',
            'variants.*.stock' => 'required|integer|min:0',
            'images.*' => 'image|max:10240',
            'new_images.*' => 'image|max:10240',
        ]);
    }

    private function resolveClothingLineId(?string $name): ?int
    {
        if (empty($name)) return null;
        $slug = Str::slug($name);
        // firstOrCreate внутри транзакции безопасен
        $line = ClothingLine::firstOrCreate(['slug' => $slug], ['name' => $name]);
        return $line->id;
    }

    private function syncAttributesData($type, $sizes)
    {
        $typeSlug = Str::slug($type);
        AttributeOption::firstOrCreate(['type' => 'product_type', 'slug' => $typeSlug], ['value' => $type]);
        foreach ($sizes as $sizeName) {
            $sizeSlug = Str::slug($sizeName);
            AttributeOption::firstOrCreate(['type' => 'size', 'slug' => $sizeSlug], ['value' => $sizeName]);
        }
    }

    private function syncCategories(Product $product, array $categoriesInput)
    {
        $categoryIds = [];
        foreach ($categoriesInput as $input) {
            if (is_numeric($input)) {
                $categoryIds[] = $input;
            } else {
                $slug = Str::slug($input);
                $newCat = Category::firstOrCreate(['slug' => $slug], ['name' => $input]);
                $categoryIds[] = $newCat->id;
            }
        }
        $product->categories()->sync($categoryIds);
    }

    private function syncImages(Request $request, Product $product)
    {
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('media', 'tenant');
                ProductImage::create(['product_id' => $product->id, 'path' => $path, 'sort_order' => $index]);
            }
        }
    }

    private function syncImagesUpdate(Request $request, Product $product)
    {
        if ($request->hasFile('new_images')) {
            $maxOrder = $product->images()->max('sort_order') ?? -1;
            foreach ($request->file('new_images') as $file) {
                $path = $file->store('media', 'tenant');
                ProductImage::create(['product_id' => $product->id, 'path' => $path, 'sort_order' => ++$maxOrder]);
            }
        }
        if ($request->filled('deleted_images')) {
            $images = ProductImage::whereIn('id', $request->input('deleted_images'))->where('product_id', $product->id)->get();
            foreach ($images as $img) {
                Storage::disk('tenant')->delete($img->path);
                $img->delete();
            }
        }
        if ($request->filled('sorted_images_ids')) {
            $sortedIds = explode(',', $request->input('sorted_images_ids'));
            foreach ($sortedIds as $index => $imgId) {
                ProductImage::where('id', $imgId)->where('product_id', $product->id)->update(['sort_order' => $index]);
            }
        }
    }

    private function syncVariants(Product $product, array $variantsInput)
    {
        // Удаляем старые, пишем новые. При транзакции это безопасно.
        $product->variants()->delete();
        foreach ($variantsInput as $v) {
            if (!empty($v['size'])) {
                ProductVariant::create([
                    'product_id' => $product->id, 
                    'size' => $v['size'], 
                    'stock' => (int)($v['stock'] ?? 0)
                ]);
            }
        }
    }
    
    // Метод getAllTenantsProducts сокращен для краткости, он использует ту же логику что и раньше
    private function getAllTenantsProducts(Request $request) {
        $allProducts = new Collection();
        foreach (config('tenants.tenants') as $id => $config) {
            try {
                $this->tenantService->switchTenant($id);
                // Важно: подгружаем clothingLine здесь тоже
                $query = Product::with(['categories', 'images', 'variants', 'clothingLine'])->latest()->take(5);
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
}