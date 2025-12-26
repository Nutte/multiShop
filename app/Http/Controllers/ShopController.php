<?php
// FILE: app/Http/Controllers/ShopController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
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

    public function index()
    {
        $tenantId = $this->resolveTenant();

        // Получаем товары и категории текущего магазина
        $products = Product::with('clothingLine')
            ->where('stock_quantity', '>', 0)
            ->latest()
            ->paginate(12);
            
        $categories = Category::all();

        // ЛОГИКА ВЫБОРА ШАБЛОНА
        // 1. Ищем уникальный шаблон: resources/views/tenants/{id}/home.blade.php
        $view = "tenants.{$tenantId}.home";
        
        // 2. Если его нет (мы их удалили для унификации), берем ОБЩИЙ: resources/views/shop/home.blade.php
        if (!view()->exists($view)) {
            $view = 'shop.home'; // БЫЛО: shop.fallback_home (ОШИБКА) -> СТАЛО: shop.home
        }

        return view($view, compact('products', 'categories'));
    }

    public function show($slug)
    {
        $tenantId = $this->resolveTenant();

        $product = Product::where('slug', $slug)
            ->with(['variants', 'clothingLine', 'images'])
            ->firstOrFail();

        // ЛОГИКА ВЫБОРА ШАБЛОНА ТОВАРА
        $view = "tenants.{$tenantId}.product";
        
        if (!view()->exists($view)) {
            $view = 'shop.product'; // Универсальная страница товара
        }

        return view($view, compact('product'));
    }
}