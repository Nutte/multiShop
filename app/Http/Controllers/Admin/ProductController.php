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

    // Хелпер: Получить товары со всех магазинов (Для Супер-Админа)
    private function getAllTenantsProducts(Request $request)
    {
        $allProducts = new Collection();
        $tenants = config('tenants.tenants');

        foreach ($tenants as $id => $config) {
            // Переключаемся
            $this->tenantService->switchTenant($id);
            
            // Строим запрос
            $query = Product::with('categories')->latest()->take(10); // Берем последние 10 с каждого
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")->orWhere('sku', 'ilike', "%{$search}%");
                });
            }
            // (Другие фильтры для All mode опустим для производительности, но поиск оставим)

            $products = $query->get();
            
            // Добавляем метку магазина
            $products->each(function($p) use ($config, $id) {
                $p->tenant_name = $config['name'];
                $p->tenant_id = $id;
                $p->preview_url = "http://{$config['domain']}/products/{$p->slug}?preview=true";
            });

            $allProducts = $allProducts->merge($products);
        }

        return $allProducts; // Это коллекция, не пагинатор
    }

    public function index(Request $request)
    {
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $selectedTenant = $request->get('tenant_id');

        // СЦЕНАРИЙ 1: Супер-Админ смотрит "ВСЁ" (по умолчанию)
        if ($isSuperAdmin && empty($selectedTenant)) {
            $products = $this->getAllTenantsProducts($request);
            $currentTenantId = null;
            $categories = new Collection(); // В режиме ALL категории сложно объединить (у них разные ID)
            $sizes = new Collection();
            $types = new Collection();
            
            return view('admin.products.index', compact('products', 'categories', 'sizes', 'types', 'currentTenantId'));
        }

        // СЦЕНАРИЙ 2: Выбран конкретный магазин (или это Менеджер)
        if ($isSuperAdmin && $selectedTenant) {
            $this->tenantService->switchTenant($selectedTenant);
            $currentTenantId = $selectedTenant;
        } else {
            // Менеджер уже в своем контексте
            $currentTenantId = $this->tenantService->getCurrentTenantId();
        }

        // Обычный запрос внутри одного тенанта
        $query = Product::with('categories');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")->orWhere('sku', 'ilike', "%{$search}%");
            });
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
        
        // Генерация превью
        $tenantDomain = config('tenants.tenants.' . $currentTenantId . '.domain');
        foreach ($products as $product) {
            $product->preview_url = "http://{$tenantDomain}/products/{$product->slug}?preview=true";
            $product->tenant_name = config("tenants.tenants.{$currentTenantId}.name");
        }

        $categories = Category::orderBy('name')->get();
        $sizes = AttributeOption::where('type', 'size')->orderBy('value')->get();
        $types = AttributeOption::where('type', 'product_type')->orderBy('value')->get();

        return view('admin.products.index', compact('products', 'categories', 'sizes', 'types', 'currentTenantId'));
    }

    public function create()
    {
        // Если супер-админ нажал Create из режима "ALL", форсируем выбор магазина
        if (auth()->user()->role === 'super_admin' && !$this->tenantService->getCurrentTenantId()) {
            // Берем первый магазин по умолчанию, чтобы форма открылась
             $first = array_key_first(config('tenants.tenants'));
             $this->tenantService->switchTenant($first);
             $currentTenantId = $first;
        } else {
             $currentTenantId = $this->tenantService->getCurrentTenantId();
        }

        $categories = Category::all();
        $sizes = AttributeOption::where('type', 'size')->get();
        $types = AttributeOption::where('type', 'product_type')->get();

        return view('admin.products.create', compact('categories', 'sizes', 'types', 'currentTenantId'));
    }

    public function store(Request $request)
    {
        // 1. Установка контекста для Супер-Админа
        if (auth()->user()->role === 'super_admin' && $request->has('target_tenant')) {
            $this->tenantService->switchTenant($request->target_tenant);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'categories' => 'required|array', // Массив ID или строк
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'required|string|max:50', 
            'image' => 'nullable|image|max:2048',
            'attributes_type' => 'required|string', 
            'attributes_size' => 'nullable|array', 
        ]);

        // 2. АВТО-СОЗДАНИЕ КАТЕГОРИЙ (Create on fly)
        $categoryIds = [];
        foreach ($request->categories as $input) {
            if (is_numeric($input)) {
                $categoryIds[] = $input;
            } else {
                // Если пришла строка "New Category" -> создаем её
                $newCat = Category::firstOrCreate(
                    ['name' => $input],
                    ['slug' => Str::slug($input)]
                );
                $categoryIds[] = $newCat->id;
            }
        }

        // 3. АВТО-СОЗДАНИЕ АТРИБУТОВ
        // Тип
        AttributeOption::firstOrCreate(
            ['type' => 'product_type', 'value' => $request->attributes_type],
            ['slug' => Str::slug($request->attributes_type)]
        );
        // Размеры
        $sizes = $request->attributes_size ?? [];
        foreach ($sizes as $sizeName) {
            AttributeOption::firstOrCreate(
                ['type' => 'size', 'value' => $sizeName],
                ['slug' => Str::slug($sizeName)]
            );
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('media', 'tenant');
        }

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
                'size' => $sizes,
            ]
        ]);

        $product->categories()->sync($categoryIds);

        // Возвращаем на список конкретного магазина, где создали товар
        return redirect()->route('admin.products.index', ['tenant_id' => $this->tenantService->getCurrentTenantId()])
                         ->with('success', 'Product created successfully.');
    }

    public function edit(Request $request, $id)
    {
        // Восстанавливаем контекст из URL, если есть
        if ($request->has('tenant_id') && auth()->user()->role === 'super_admin') {
            $this->tenantService->switchTenant($request->tenant_id);
        }

        $product = Product::with('categories')->findOrFail($id);
        $categories = Category::all();
        $sizes = AttributeOption::where('type', 'size')->get();
        $types = AttributeOption::where('type', 'product_type')->get();
        
        $tenantDomain = config('tenants.tenants.' . $this->tenantService->getCurrentTenantId() . '.domain');
        $previewUrl = "http://{$tenantDomain}/products/{$product->slug}?preview=true";

        return view('admin.products.edit', compact('product', 'categories', 'previewUrl', 'sizes', 'types'));
    }

    // Update и Destroy аналогичны, главное - контекст
    public function update(Request $request, $id) { /* ... код без изменений ... */ }
    
    public function destroy(Request $request, $id)
    {
        if ($request->has('tenant_id')) {
             $this->tenantService->switchTenant($request->tenant_id);
        }
        
        $product = Product::findOrFail($id);
        if ($product->image_path) Storage::disk('tenant')->delete($product->image_path);
        $product->delete();
        
        return back()->with('success', 'Product deleted.');
    }
}