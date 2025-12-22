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

class ProductController extends Controller
{
    protected TenantService $tenantService;
    protected ProductService $productService;

    public function __construct(TenantService $tenantService, ProductService $productService)
    {
        $this->tenantService = $tenantService;
        $this->productService = $productService;
    }

    private function checkContext()
    {
        if (auth()->user()->role === 'super_admin' && !$this->tenantService->getCurrentTenantId()) {
            return false;
        }
        return true;
    }

    public function index()
    {
        if (!$this->checkContext()) return view('admin.products.select_tenant');
        // Подгружаем категории для отображения в списке
        $products = Product::with('categories')->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        if (!$this->checkContext()) return view('admin.products.select_tenant');
        
        $categories = Category::all();
        // Загружаем доступные опции для подсказок
        $sizes = AttributeOption::where('type', 'size')->get();
        $types = AttributeOption::where('type', 'product_type')->get();

        return view('admin.products.create', compact('categories', 'sizes', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            // categories теперь массив, может содержать ID или строки (новые названия)
            'categories' => 'required|array', 
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'required|string|max:50|unique:products,sku', 
            'image' => 'nullable|image|max:2048',
            // Атрибуты тоже могут быть новыми
            'attributes_type' => 'required|string', 
            'attributes_size' => 'nullable|array', 
        ]);

        // 1. Обработка КАТЕГОРИЙ (Create on fly)
        $categoryIds = [];
        foreach ($request->categories as $input) {
            if (is_numeric($input)) {
                $categoryIds[] = $input;
            } else {
                // Это новая категория (строка)
                $newCat = Category::firstOrCreate(
                    ['name' => $input],
                    ['slug' => Str::slug($input)]
                );
                $categoryIds[] = $newCat->id;
            }
        }

        // 2. Обработка ТИПА ПРОДУКТА (Справочник)
        $typeName = $request->attributes_type;
        AttributeOption::firstOrCreate(
            ['type' => 'product_type', 'value' => $typeName],
            ['slug' => Str::slug($typeName)]
        );

        // 3. Обработка РАЗМЕРОВ (Справочник)
        $sizes = $request->attributes_size ?? [];
        foreach ($sizes as $sizeName) {
            AttributeOption::firstOrCreate(
                ['type' => 'size', 'value' => $sizeName],
                ['slug' => Str::slug($sizeName)]
            );
        }

        // 4. Картинка
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('media', 'tenant');
        }

        // 5. Создание товара
        $product = $this->productService->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
            'price' => $validated['price'],
            'sku' => $validated['sku'],
            'stock_quantity' => $validated['stock_quantity'],
            'description' => $request->input('description'),
            'image_path' => $imagePath,
            'attributes' => [
                'type' => $typeName,
                'size' => $sizes,
            ]
        ]);

        // 6. Привязка категорий
        $product->categories()->sync($categoryIds);

        return redirect()->route('admin.products.index')->with('success', 'Product created with dynamic attributes.');
    }

    public function edit($id)
    {
        if (!$this->checkContext()) return view('admin.products.select_tenant');

        $product = Product::with('categories')->findOrFail($id);
        $categories = Category::all();
        $sizes = AttributeOption::where('type', 'size')->get();
        $types = AttributeOption::where('type', 'product_type')->get();
        
        $tenantDomain = config('tenants.tenants.' . $this->tenantService->getCurrentTenantId() . '.domain');
        $previewUrl = "http://{$tenantDomain}/products/{$product->slug}?preview=true";

        return view('admin.products.edit', compact('product', 'categories', 'previewUrl', 'sizes', 'types'));
    }

    public function update(Request $request, $id)
    {
        if (!$this->checkContext()) return view('admin.products.select_tenant');
        
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'categories' => 'required|array',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'required|string|max:50',
            'image' => 'nullable|image|max:2048',
            'attributes_type' => 'required|string', 
            'attributes_size' => 'nullable|array', 
        ]);

        // Обработка Категорий
        $categoryIds = [];
        foreach ($request->categories as $input) {
            if (is_numeric($input)) {
                $categoryIds[] = $input;
            } else {
                $newCat = Category::firstOrCreate(['name' => $input], ['slug' => Str::slug($input)]);
                $categoryIds[] = $newCat->id;
            }
        }

        // Обработка Атрибутов
        $typeName = $request->attributes_type;
        AttributeOption::firstOrCreate(['type' => 'product_type', 'value' => $typeName], ['slug' => Str::slug($typeName)]);
        
        $sizes = $request->attributes_size ?? [];
        foreach ($sizes as $sizeName) {
            AttributeOption::firstOrCreate(['type' => 'size', 'value' => $sizeName], ['slug' => Str::slug($sizeName)]);
        }

        // Картинка
        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('tenant')->delete($product->image_path);
            }
            $product->image_path = $request->file('image')->store('media', 'tenant');
        }

        $product->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'sku' => $validated['sku'],
            'stock_quantity' => $validated['stock_quantity'],
            'description' => $request->input('description'),
            'attributes' => [
                'type' => $typeName,
                'size' => $sizes,
            ]
        ]);

        $product->categories()->sync($categoryIds);

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy($id)
    {
        if (!$this->checkContext()) return view('admin.products.select_tenant');
        
        $product = Product::findOrFail($id);
        if ($product->image_path) Storage::disk('tenant')->delete($product->image_path);
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }
}