<?php
// FILE: app/Http/Controllers/ShopController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ClothingLine;
use App\Models\ProductVariant;
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

        $products = Product::with('clothingLine')
            ->where('stock_quantity', '>', 0)
            ->latest()
            ->paginate(12);
            
        $categories = Category::all();

        $view = "tenants.{$tenantId}.home";
        
        if (!view()->exists($view)) {
            $view = 'shop.home';
        }

        return view($view, compact('products', 'categories'));
    }

    public function show($slug)
    {
        $tenantId = $this->resolveTenant();

        $product = Product::where('slug', $slug)
            ->with(['variants', 'clothingLine', 'images'])
            ->firstOrFail();

        $view = "tenants.{$tenantId}.product";
        
        if (!view()->exists($view)) {
            $view = 'shop.product';
        }

        return view($view, compact('product'));
    }

    public function products(Request $request)
    {
        $tenantId = $this->resolveTenant();
        
        // Базовый запрос с подгрузкой связей
        $query = Product::with(['categories', 'clothingLine', 'variants', 'images'])
            ->where('stock_quantity', '>', 0);
        
        // 🔹 ПРЕСЕТЫ: НОВИНКИ и СКИДКИ
        if ($request->filled('preset')) {
            switch ($request->preset) {
                case 'new':
                    // Товары за последние 30 дней
                    $query->where('created_at', '>=', now()->subDays(30));
                    break;
                case 'discount':
                    // Товары со скидкой
                    $query->whereNotNull('sale_price')
                          ->whereColumn('sale_price', '<', 'price');
                    break;
                case 'bestsellers':
                    // Самые продаваемые (пока заглушка - можно доработать с учетом заказов)
                    $query->orderBy('stock_quantity', 'desc'); // временно по остаткам
                    break;
            }
        }
        
        // 🔹 ФИЛЬТРАЦИЯ ПО КАТЕГОРИИ
        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        
        // 🔹 ФИЛЬТРАЦИЯ ПО РАЗМЕРУ
        if ($request->filled('size')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('size', $request->size);
            });
        }
        
        // 🔹 ФИЛЬТРАЦИЯ ПО ЛИНЕЙКЕ
        if ($request->filled('line')) {
            $query->whereHas('clothingLine', function ($q) use ($request) {
                $q->where('slug', $request->line);
            });
        }
        
        // 🔹 ФИЛЬТРАЦИЯ ПО ЦЕНЕ
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // 🔹 СОРТИРОВКА
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name');
                break;
            case 'discount':
                $query->whereNotNull('sale_price')
                      ->whereColumn('sale_price', '<', 'price')
                      ->orderByRaw('(price - sale_price) / price DESC');
                break;
            default:
                $query->latest();
                break;
        }
        
        // 🔹 СТАТИСТИКА ДЛЯ ПРЕСЕТОВ
        $stats = [
            'total' => Product::where('stock_quantity', '>', 0)->count(),
            'new' => Product::where('stock_quantity', '>', 0)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'discount' => Product::where('stock_quantity', '>', 0)
                ->whereNotNull('sale_price')
                ->whereColumn('sale_price', '<', 'price')
                ->count(),
        ];
        
        $products = $query->paginate(20)->appends($request->query());
        
        // Данные для фильтров
        $categories = Category::all();
        $clothingLines = ClothingLine::all();
        
        // Получаем уникальные размеры из вариантов текущего магазина
        $sizes = ProductVariant::select('size')->distinct()->orderBy('size')->pluck('size');
        
        // ЛОГИКА ВЫБОРА ШАБЛОНА
        $view = "tenants.{$tenantId}.products";
        
        if (!view()->exists($view)) {
            $view = 'shop.products';
        }
        
        return view($view, compact('products', 'categories', 'clothingLines', 'sizes', 'stats'));
    }
}