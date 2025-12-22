<?php
// FILE: app/Http/Controllers/ShopController.php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use App\Services\TenantService;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    protected TenantService $tenantService;
    protected CartService $cartService;

    public function __construct(TenantService $tenantService, CartService $cartService)
    {
        $this->tenantService = $tenantService;
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantService->getCurrentTenantId();
        
        $query = Product::query()->with('categories');

        // Фильтрация
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        
        $products = $query->latest()->paginate(12);
        $categories = Category::has('products')->get(); // Показываем только категории с товарами

        return view("tenants.{$tenantId}.home", compact('products', 'categories'));
    }

    public function show($slug)
    {
        $tenantId = $this->tenantService->getCurrentTenantId();
        // Загружаем товар
        $product = Product::where('slug', $slug)->firstOrFail();
        
        // Пытаемся загрузить уникальный шаблон товара для магазина, если нет - общий
        $viewName = "tenants.{$tenantId}.product";
        if (!view()->exists($viewName)) {
            // Фолбэк, если вдруг не создали (но мы создадим)
            abort(404, "View {$viewName} not found");
        }

        return view($viewName, compact('product'));
    }

    // Методы корзины без изменений...
    public function cart() { 
        return view('cart', [
            'cart' => $this->cartService->get(), 
            'total' => $this->cartService->total(), 
            'tenantId' => $this->tenantService->getCurrentTenantId()
        ]); 
    }
    public function addToCart(Request $request) { 
        $this->cartService->add((int)$request->product_id); 
        return back()->with('success', 'Added to cart!'); 
    }
    public function checkout(Request $request) { 
        return redirect()->back()->with('error', 'Checkout logic is mocked.'); 
    }
}