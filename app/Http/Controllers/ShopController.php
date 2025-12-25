<?php
// FILE: app/Http/Controllers/ShopController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ClothingLine;
use App\Services\TenantService;
use Illuminate\Http\Request;

class ShopController extends Controller
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

    public function index(Request $request)
    {
        $tenantId = $this->resolveTenant();
        
        $query = Product::with('images');

        // Фильтры
        if ($request->filled('category')) {
            $query->whereHas('categories', fn($q) => $q->where('slug', $request->category));
        }
        
        if ($request->filled('line')) {
            $query->whereHas('clothingLine', fn($q) => $q->where('slug', $request->line));
        }

        // ИСПРАВЛЕНИЕ: Используем paginate() вместо get()
        $products = $query->latest()->paginate(12)->withQueryString();
        
        $categories = Category::has('products')->get();
        $lines = ClothingLine::has('products')->get();

        // Динамический выбор шаблона
        $view = "tenants.{$tenantId}.home";
        if (!view()->exists($view)) {
            // Фолбэк на простой список, если дизайн не готов
            return view('shop.fallback_home', compact('products', 'categories', 'lines'));
        }

        return view($view, compact('products', 'categories', 'lines'));
    }

    public function show($slug)
    {
        $tenantId = $this->resolveTenant();
        
        $product = Product::with(['images', 'variants', 'categories', 'clothingLine'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Динамический выбор шаблона товара
        $view = "tenants.{$tenantId}.product";
        if (!view()->exists($view)) {
             // Можно создать generic product view
             abort(404, 'Product view not found for this store');
        }

        return view($view, compact('product'));
    }
    
    // Методы корзины (cart, addToCart, checkout) перенесены в CartController
    // Но роуты могут ссылаться сюда, если вы не обновили web.php. 
    // Убедитесь, что в routes/web.php маршруты ведут на CartController!
}