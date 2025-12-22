<?php
// FILE: app/Http/Controllers/ShopController.php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    protected TenantService $tenantService;
    protected CartService $cartService;

    public function __construct(TenantService $tenantService, CartService $cartService)
    {
        $this->tenantService = $tenantService;
        $this->cartService = $cartService;
    }

    /**
     * Главная страница магазина
     */
    public function index()
    {
        $tenantId = $this->tenantService->getCurrentTenantId();
        
        // Берем товары из текущей схемы
        $products = Product::inRandomOrder()->take(6)->get();

        // Подключаем view специфичный для тенанта
        // resources/views/tenants/{tenant}/home.blade.php
        return view("tenants.{$tenantId}.home", compact('products'));
    }

    /**
     * Добавление в корзину
     */
    public function addToCart(Request $request)
    {
        $this->cartService->add((int)$request->input('product_id'));
        return redirect()->back()->with('success', 'Product added to cart!');
    }

    /**
     * Страница корзины
     */
    public function cart()
    {
        $tenantId = $this->tenantService->getCurrentTenantId();
        $cart = $this->cartService->get();
        $total = $this->cartService->total();

        // Используем общий шаблон корзины или можно сделать специфичный
        // Для простоты сделаем общий layout, но стили будут разные
        return view('cart', compact('cart', 'total', 'tenantId'));
    }

    /**
     * Оформление заказа (Mock оплаты)
     */
    public function checkout(Request $request)
    {
        $cart = $this->cartService->get();
        if (empty($cart)) {
            return redirect('/')->with('error', 'Cart is empty');
        }

        $tenantId = $this->tenantService->getCurrentTenantId();
        
        // Генерация префикса заказа (ST-, DH-, MG-)
        $prefixMap = [
            'street_style' => 'ST',
            'designer_hub' => 'DH',
            'military_gear' => 'MG',
        ];
        $prefix = $prefixMap[$tenantId] ?? 'ORD';
        $orderNumber = $prefix . '-' . strtoupper(Str::random(6));

        // Эмуляция оплаты (PaymentMock)
        // В реальности здесь был бы запрос к API банка
        $paymentSuccess = true; 

        if ($paymentSuccess) {
            Order::create([
                'order_number' => $orderNumber,
                'total_amount' => $this->cartService->total(),
                'status' => 'paid',
                'customer_email' => $request->input('email', 'guest@example.com'),
                'items' => $cart,
            ]);

            $this->cartService->clear();

            return view('success', compact('orderNumber', 'tenantId'));
        }

        return back()->with('error', 'Payment failed');
    }
}