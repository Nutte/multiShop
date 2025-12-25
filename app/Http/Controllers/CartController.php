<?php
// FILE: app/Http/Controllers/CartController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\TelegramConfig;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    // --- HELPER: Изоляция Сессии ---
    private function getCartKey()
    {
        return 'cart_' . $this->tenantService->getCurrentTenantId();
    }

    private function getPromoKey()
    {
        return 'promo_code_' . $this->tenantService->getCurrentTenantId();
    }

    // --- HELPER: Изоляция БД ---
    private function resolveTenant()
    {
        $host = request()->getHost();
        $map = $this->tenantService->getDomainMap();
        $tenantId = $map[$host] ?? 'default';
        $this->tenantService->switchTenant($tenantId);
        return $tenantId;
    }

    // --- ЛОГИКА КОРЗИНЫ ---

    public function index()
    {
        $tenantId = $this->resolveTenant();
        $cartKey = $this->getCartKey();
        $promoKey = $this->getPromoKey();
        
        $cart = session()->get($cartKey, []);
        $promoCode = session()->get($promoKey, null);
        
        $cartItems = [];
        $subtotal = 0;
        $discount = 0;

        foreach ($cart as $key => $item) {
            $product = Product::find($item['product_id']);
            
            if (!$product) {
                unset($cart[$key]);
                continue;
            }
            
            $price = $product->current_price;
            $lineTotal = $price * $item['quantity'];
            $subtotal += $lineTotal;

            $cartItems[] = [
                'product' => $product,
                'size' => $item['size'],
                'quantity' => $item['quantity'],
                'price' => $price,
                'total' => $lineTotal,
                'row_id' => $key
            ];
        }
        
        session()->put($cartKey, $cart);

        // Логика промокода
        if ($promoCode) {
            $promo = PromoCode::where('code', $promoCode)->first();
            
            if ($promo && $promo->isValid()) {
                $scopeData = $promo->scope_data ?? [];
                if ($promo->scope_type === 'global' || isset($scopeData[$tenantId])) {
                    if ($promo->type === 'fixed') {
                        $discount = $promo->value;
                    } else {
                        $discount = ($subtotal * $promo->value) / 100;
                    }
                } else {
                    session()->forget($promoKey);
                }
            } else {
                session()->forget($promoKey);
            }
        }

        $total = max(0, $subtotal - $discount);

        $view = "tenants.{$tenantId}.cart";
        if (!view()->exists($view)) {
            $view = 'cart.index';
        }

        return view($view, compact('cartItems', 'subtotal', 'discount', 'total', 'promoCode'));
    }

    public function addToCart(Request $request)
    {
        $this->resolveTenant();
        
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size' => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);
        $size = $request->size ?? 'One Size';
        
        $rowId = $product->id . '_' . $size;
        $cartKey = $this->getCartKey();

        $cart = session()->get($cartKey, []);

        if (isset($cart[$rowId])) {
            $cart[$rowId]['quantity']++;
        } else {
            $cart[$rowId] = [
                'product_id' => $product->id,
                'size' => $size,
                'quantity' => 1
            ];
        }

        session()->put($cartKey, $cart);

        return redirect()->route('cart.index')->with('success', 'Product added to cart!');
    }

    public function removeFromCart($rowId)
    {
        $this->resolveTenant();
        $cartKey = $this->getCartKey();
        
        $cart = session()->get($cartKey, []);
        if (isset($cart[$rowId])) {
            unset($cart[$rowId]);
            session()->put($cartKey, $cart);
        }
        return back()->with('success', 'Item removed.');
    }

    public function applyPromo(Request $request)
    {
        $this->resolveTenant();
        
        $request->validate(['code' => 'required|string']);
        $code = Str::upper($request->code);

        $promo = PromoCode::where('code', $code)->first();

        if (!$promo || !$promo->isValid()) {
            return back()->with('error', 'Invalid or expired promo code.');
        }

        session()->put($this->getPromoKey(), $code);

        return back()->with('success', 'Promo code applied!');
    }

    // --- ОФОРМЛЕНИЕ ЗАКАЗА ---

    public function checkout(Request $request)
    {
        $this->resolveTenant();
        
        // 1. ВАЛИДАЦИЯ (с проверкой телефона UA)
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => [
                'required',
                'string',
                // Регулярное выражение для украинских номеров:
                // Принимает: +380971234567, 0971234567
                'regex:/^(\+380|0)[0-9]{9}$/'
            ],
            'shipping_method' => 'required|in:nova_poshta,courier,pickup',
            'shipping_address' => 'required|string|min:5',
        ], [
            // Кастомное сообщение об ошибке
            'customer_phone.regex' => 'Please enter a valid Ukrainian phone number (e.g., 0971234567 or +38097...)'
        ]);

        $cartKey = $this->getCartKey();
        $promoKey = $this->getPromoKey();

        $cart = session()->get($cartKey, []);
        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty.');
        }

        // 2. Проверка стоков и расчет
        $subtotal = 0;
        $orderItemsData = [];
        
        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            $variant = $product->variants()->where('size', $item['size'])->first();
            
            if ($variant && $variant->stock < $item['quantity']) {
                return back()->with('error', "Sorry, size {$item['size']} for {$product->name} is out of stock.");
            }
            if (!$variant && $product->stock_quantity < $item['quantity']) {
                return back()->with('error', "Sorry, {$product->name} is out of stock.");
            }

            $price = $product->current_price;
            $lineTotal = $price * $item['quantity'];
            $subtotal += $lineTotal;

            $orderItemsData[] = [
                'product' => $product,
                'size' => $item['size'],
                'quantity' => $item['quantity'],
                'price' => $price,
                'total' => $lineTotal,
            ];
        }

        $discount = 0;
        $promoCode = session()->get($promoKey);
        
        if ($promoCode) {
            $promo = PromoCode::where('code', $promoCode)->first();
            if ($promo && $promo->isValid()) {
                 $discount = ($promo->type === 'fixed') ? $promo->value : ($subtotal * $promo->value / 100);
            }
        }
        $total = max(0, $subtotal - $discount);

        // 3. Создание заказа
        try {
            DB::transaction(function () use ($validated, $subtotal, $discount, $total, $promoCode, $orderItemsData) {
                // Здесь shipping_method уже точно существует в БД после миграции
                $order = Order::create([
                    'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'customer_phone' => $validated['customer_phone'],
                    'shipping_method' => $validated['shipping_method'],
                    'shipping_address' => $validated['shipping_address'],
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                    'promo_code' => $promoCode,
                    'status' => 'new',
                    'payment_method' => 'cod',
                ]);

                foreach ($orderItemsData as $data) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $data['product']->id,
                        'product_name' => $data['product']->name,
                        'sku' => $data['product']->sku,
                        'size' => $data['size'],
                        'quantity' => $data['quantity'],
                        'price' => $data['price'],
                        'total' => $data['total'],
                    ]);

                    $variant = $data['product']->variants()->where('size', $data['size'])->first();
                    if ($variant) {
                        $variant->decrement('stock', $data['quantity']);
                    }
                    $data['product']->decrement('stock_quantity', $data['quantity']);
                }

                $this->sendTelegramNotification($order);
            });

            session()->forget([$cartKey, $promoKey]);

            return redirect()->route('home')->with('success', 'Thank you! Your order has been placed successfully.');

        } catch (\Exception $e) {
            Log::error("Checkout Error: " . $e->getMessage());
            return back()->with('error', 'System error during checkout: ' . $e->getMessage());
        }
    }

    private function sendTelegramNotification($order)
    {
        $tenantId = $this->tenantService->getCurrentTenantId();
        
        $config = TelegramConfig::where('tenant_id', $tenantId)->where('is_active', true)->first();
        if (!$config) {
            $config = TelegramConfig::whereNull('tenant_id')->where('is_active', true)->first();
        }

        if ($config) {
            $itemsList = "";
            foreach ($order->items as $item) {
                $itemsList .= "- {$item->product_name} ({$item->size}) x{$item->quantity}\n";
            }

            $message = "🆕 *New Order #{$order->order_number}*\n" .
                       "Store: " . strtoupper($tenantId) . "\n" .
                       "Customer: {$order->customer_name}\n" .
                       "Phone: {$order->customer_phone}\n" .
                       "Total: *$" . $order->total_amount . "*\n" .
                       "----------------\n" .
                       $itemsList . 
                       "\nMethod: {$order->shipping_method}\n" .
                       "Addr: {$order->shipping_address}";

            try {
                Http::post("https://api.telegram.org/bot{$config->bot_token}/sendMessage", [
                    'chat_id' => $config->chat_id,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);
            } catch (\Exception $e) {
                Log::error("Telegram Send Error: " . $e->getMessage());
            }
        }
    }
}