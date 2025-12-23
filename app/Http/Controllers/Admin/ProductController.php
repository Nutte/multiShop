<?php
// FILE: app/Http/Controllers/Admin/ProductController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage; // Не забудь импортировать модель
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

    // --- ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ---

    private function resolveContext(Request $request)
    {
        $user = auth()->user();

        // 1. Менеджер: Всегда в своем контексте
        if ($user->role !== 'super_admin') {
            return $this->tenantService->getCurrentTenantId();
        }

        // 2. Супер-Админ: переключаемся, если передан ID
        $tenantId = $request->get('tenant_id');
        if ($tenantId) {
            $this->tenantService->switchTenant($tenantId);
            return $tenantId;
        }

        // Если контекст уже есть
        $current = $this->tenantService->getCurrentTenantId();
        if ($current) return $current;

        return null;
    }

    // Получить товары всех магазинов (для списка "ALL STORES")
    private function getAllTenantsProducts(Request $request)
    {
        $allProducts = new Collection();
        foreach (config('tenants.tenants') as $id => $config) {
            try {
                $this->tenantService->switchTenant($id);
                $query = Product::with(['categories', 'images'])->latest()->take(5);
                
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

    // --- ОСНОВНЫЕ МЕТОДЫ ---

    public function index(Request $request)
    {
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $selectedTenant = $request->get('tenant_id');
        
        // Режим "ВСЕ МАГАЗИНЫ"
        if ($isSuperAdmin && empty($selectedTenant)) {
            $products = $this->getAllTenantsProducts($request);
            // Пустые коллекции для фильтров, так как в режиме ALL они не работают
            $categories = new Collection(); $sizes = new Collection(); $types = new Collection();
            $currentTenantId = null;
            return view('admin.products.index', compact('products', 'categories', 'sizes', 'types', 'currentTenantId'));
        }

        // Режим "ОДИН МАГАЗИН"
        $currentTenantId = $this->resolveContext($request);
        
        // Если Супер-Админ без контекста -> берем первый магазин по умолчанию
        if (!$currentTenantId && $isSuperAdmin) {
             $first = array_key_first(config('tenants.tenants'));
             $this->tenantService->switchTenant($first);
             $currentTenantId = $first;
        }

        $query = Product::with(['categories', 'images']);

        // Фильтры
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

    // Единый метод для показа формы (Create/Edit)
    public function form(Request $request, $id = null)
    {
        $currentTenantId = $this->resolveContext($request);

        if (!$currentTenantId && auth()->user()->role === 'super_admin') {
             $first = array_key_first(config('tenants.tenants'));
             $this->tenantService->switchTenant($first);
             $currentTenantId = $first;
        }

        if ($id) {
            $product = Product::with(['categories', 'images'])->findOrFail($id);
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

    // Перенаправления для Resource Controller
    public function create(Request $request) { return $this->form($request); }
    public function edit(Request $request, $id) { return $this->form($request, $id); }

    // --- СОХРАНЕНИЕ (STORE) ---
    public function store(Request $request)
    {
        if (auth()->user()->role === 'super_admin' && $request->has('target_tenant')) {
            $this->tenantService->switchTenant($request->target_tenant);
        } else {
            $this->resolveContext($request);
        }

        $validated = $this->validateProduct($request);

        // 1. Создаем атрибуты в справочнике
        $this->syncAttributes($request);

        // 2. Создаем сам товар
        $product = $this->productService->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
            'price' => $validated['price'],
            'sku' => $validated['sku'],
            'stock_quantity' => $validated['stock_quantity'],
            'description' => $request->input('description'),
            'attributes' => [
                'type' => $request->attributes_type,
                'size' => $request->attributes_size ?? [],
            ]
        ]);

        // 3. Сохраняем ИЗОБРАЖЕНИЯ (Множественная загрузка)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('media', 'tenant');
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'sort_order' => $index // 0 станет обложкой
                ]);
            }
        }

        // 4. Привязываем категории
        $this->syncCategories($product, $request->categories);

        return redirect()->route('admin.products.index', ['tenant_id' => $this->tenantService->getCurrentTenantId()])
                         ->with('success', 'Product created successfully.');
    }

    // --- ОБНОВЛЕНИЕ (UPDATE) ---
    public function update(Request $request, $id)
    {
        $this->resolveContext($request);
        $product = Product::findOrFail($id);
        
        $validated = $this->validateProduct($request, $id);
        
        $this->syncAttributes($request);

        // 1. Обработка НОВЫХ изображений
        if ($request->hasFile('new_images')) {
            // Находим последний индекс сортировки, чтобы добавить новые в конец
            $maxOrder = $product->images()->max('sort_order') ?? -1;
            
            foreach ($request->file('new_images') as $file) {
                $path = $file->store('media', 'tenant');
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'sort_order' => ++$maxOrder
                ]);
            }
        }

        // 2. Обработка УДАЛЕНИЯ выбранных изображений
        if ($request->filled('deleted_images')) {
            $idsToDelete = $request->input('deleted_images');
            $images = ProductImage::whereIn('id', $idsToDelete)
                                  ->where('product_id', $product->id)
                                  ->get();
            
            foreach ($images as $img) {
                // Удаляем файл с диска
                Storage::disk('tenant')->delete($img->path);
                // Удаляем запись из БД
                $img->delete();
            }
        }

        // 3. Обработка СОРТИРОВКИ (Drag & Drop)
        if ($request->filled('sorted_images_ids')) {
            $sortedIds = explode(',', $request->input('sorted_images_ids'));
            // Проходимся по списку ID и обновляем порядок
            foreach ($sortedIds as $index => $imgId) {
                ProductImage::where('id', $imgId)
                            ->where('product_id', $product->id)
                            ->update(['sort_order' => $index]);
            }
        }

        // 4. Обновляем данные товара
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

    // --- УДАЛЕНИЕ (DESTROY) ---
    public function destroy(Request $request, $id)
    {
        $this->resolveContext($request);
        $product = Product::findOrFail($id);
        
        // Удаляем все картинки с диска
        foreach ($product->images as $img) {
            Storage::disk('tenant')->delete($img->path);
        }
        // Удаляем записи картинок (каскадно удалились бы, но лучше явно)
        $product->images()->delete();
        
        // Удаляем товар
        $product->delete();
        
        return back()->with('success', 'Product and images deleted.');
    }

    // --- ВНУТРЕННИЕ МЕТОДЫ ---

    private function validateProduct(Request $request, $id = null)
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'categories' => 'required|array',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'required|string|max:50',
            
            // Валидация массивов картинок
            'images.*' => 'image|max:10240', // При создании (до 10МБ)
            'new_images.*' => 'image|max:10240', // При обновлении
            
            'attributes_type' => 'required|string', 
            'attributes_size' => 'nullable|array', 
        ]);
    }

    private function syncAttributes(Request $request)
    {
        // Используем поиск по слагу для избежания дублей
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
                // Используем поиск по слагу
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