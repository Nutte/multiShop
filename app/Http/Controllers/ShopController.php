<?php
// FILE: app/Http/Controllers/ShopController.php

namespace App\Http\Controllers;

use App\Services\TenantService;
use App\Models\Product;
use App\Models\Category;
use App\Models\ClothingLine;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index(Request $request)
    {
        // Определяем текущий магазин по домену
        $host = $request->getHost();
        $map = $this->tenantService->getDomainMap();
        
        $tenantId = $map[$host] ?? 'default'; 
        // Если домен не найден, можно переключить на дефолтный или выбросить 404
        
        try {
            $this->tenantService->switchTenant($tenantId);
        } catch (\Exception $e) {
            abort(404, 'Store not found');
        }

        $query = Product::with(['images', 'clothingLine']); // Жадная загрузка

        // Фильтр по Категории (если есть)
        if ($request->has('category')) {
            $query->whereHas('categories', fn($q) => $q->where('slug', $request->category));
        }

        // НОВЫЙ ФИЛЬТР: По Линейке
        if ($request->has('line')) {
            $query->whereHas('clothingLine', fn($q) => $q->where('slug', $request->line));
        }

        $products = $query->latest()->get();
        
        // Для меню
        $categories = Category::has('products')->get();
        $lines = ClothingLine::has('products')->get();

        // Выбираем шаблон в зависимости от магазина
        // resources/views/tenants/{tenant_id}/home.blade.php
        $view = "tenants.{$tenantId}.home";
        if (!view()->exists($view)) {
            $view = 'tenants.default.home'; // Фолбэк
        }

        return view($view, compact('products', 'categories', 'lines', 'tenantId'));
    }

    public function show(Request $request, $slug)
    {
        // Аналогичное определение тенанта
        $host = $request->getHost();
        $map = $this->tenantService->getDomainMap();
        $tenantId = $map[$host] ?? 'default';
        $this->tenantService->switchTenant($tenantId);

        $product = Product::with(['images', 'variants', 'clothingLine', 'categories'])->where('slug', $slug)->firstOrFail();

        $view = "tenants.{$tenantId}.product";
        if (!view()->exists($view)) {
            $view = 'tenants.default.product';
        }

        return view($view, compact('product'));
    }

    // Остальные методы (cart, checkout) ...
    public function cart() { return view('cart.index'); }
    public function addToCart() { /* ... */ }
    public function checkout() { /* ... */ }
}