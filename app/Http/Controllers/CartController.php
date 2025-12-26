<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\TelegramConfig;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CartController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    // --- HELPER: Нормализация телефона ---
    // Приводит номера (097..., 38097...) к единому формату +380...
    private function normalizePhone($phone)
    {
        // Удаляем все лишние символы
        $clean = preg_replace('/[^0-9+]/', '', $phone);
        
        // Если начинается с 0 (097...), добавляем +38
        if (str_starts_with($clean, '0')) {
            return '+38' . $clean;
        }
        
        // Если начинается с 380 (без плюса), добавляем плюс
        if (str_starts_with($clean, '380')) {
            return '+' . $clean;
        }

        return $clean;
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
    // Переключает схему БД в зависимости от домена
    private function resolveTenant()
    {
        $host = request()->getHost();
        $map = $this->tenantService->getDomainMap();
        $tenantId = $map[$host] ?? 'default';
        $this->tenantService->switchTenant($tenantId);
        return $tenantId;
    }

    // --- ПРОСМОТР КОРЗИНЫ ---
    public function index()
    {
        $tenantId = $this->resolveTenant();
        $cartKey = $this->getCartKey();
        $promoKey = $this->getPromoKey();
        
        // Получаем корзину текущего магазина
        $cart = session()->get($cartKey, []);
        $promoCode = session()->get($promoKey, null);
        
        $cartItems = [];
        $subtotal = 0;
        $discount = 0;

        foreach ($cart as $key => $item) {
            // Ищем товар в БД текущего магазина
            $product = Product::find($item['product_id']);
            
            // Если товар удален - убираем из сессии
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
        
        // Обновляем сессию (если удаляли товары)
        session()->put($cartKey, $cart);

        // Логика промокода
        if ($promoCode) {
            $promo = PromoCode::where('code', $promoCode)->first();
            
            if ($promo && $promo->isValid()) {
                $scopeData = $promo->scope_data ?? [];
                
                // Проверка привязки промокода к магазину
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

        // Ищем уникальный шаблон корзины, если нет — берем общий
        $view = "tenants.{$tenantId}.cart";
        if (!view()->exists($view)) {
            $view = 'cart.index';
        }

        return view($view, compact('cartItems', 'subtotal', 'discount', 'total', 'promoCode'));
    }

    // --- ДОБАВЛЕНИЕ ТОВАРА ---
    public function addToCart(Request $request)
    {
        $this->resolveTenant();
        
        $product = Product::findOrFail($request->product_id);
        
        // Проверяем, есть ли варианты размеров у товара
        $hasVariants = $product->variants()->count() > 0;

        $request->validate([
            'product_id' => 'required|exists:products,id',
            // Если варианты есть, размер обязателен
            'size' => $hasVariants ? 'required|string' : 'nullable|string',
        ]);

        $size = $request->size ?? 'One Size';
        
        // Дополнительная проверка наличия варианта
        if ($hasVariants) {
            $variant = $product->variants()->where('size', $size)->first();
            if (!$variant) {
                return back()->with('error', 'Selected size is invalid.');
            }
            if ($variant->stock <= 0) {
                return back()->with('error', 'Selected size is out of stock.');
            }
        } else {
             if ($product->stock_quantity <= 0) {
                 return back()->with('error', 'Product is out of stock.');
             }
        }
        
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

    // --- УДАЛЕНИЕ ИЗ КОРЗИНЫ ---
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

    // --- ПРОМОКОД ---
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

    // --- ОФОРМЛЕНИЕ ЗАКАЗА (CHECKOUT) ---
    public function checkout(Request $request)
    {
        $this->resolveTenant();
        
        // 1. Валидация
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            // Regex для украинских номеров (+380... или 0...)
            'customer_phone' => ['required', 'string', 'regex:/^(\+380|0)[0-9]{9}$/'],
            'shipping_method' => 'required|in:nova_poshta,courier,pickup',
            'shipping_address' => 'required|string|min:5',
        ], [
            'customer_phone.regex' => 'Please enter a valid Ukrainian phone number (e.g., 0971234567 or +380...)'
        ]);

        // Нормализуем телефон перед поиском/сохранением
        $normalizedPhone = $this->normalizePhone($validated['customer_phone']);

        $cartKey = $this->getCartKey();
        $promoKey = $this->getPromoKey();
        $cart = session()->get($cartKey, []);

        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty.');
        }

        // 2. Расчет и финальная проверка стоков
        $subtotal = 0;
        $orderItemsData = [];
        
        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;

            // Проверка стока
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

        // 3. Создание заказа (Транзакция)
        try {
            $generatedPassword = null;
            $user = null;

            DB::transaction(function () use ($validated, $normalizedPhone, $subtotal, $discount, $total, $promoCode, $orderItemsData, &$generatedPassword, &$user) {
                
                // А) Поиск или регистрация пользователя по нормализованному телефону
                $user = User::where('phone', $normalizedPhone)->first();

                if (!$user) {
                    $generatedPassword = Str::random(8); // Генерируем пароль
                    $user = User::create([
                        'name' => $validated['customer_name'],
                        'email' => $validated['customer_email'],
                        'phone' => $normalizedPhone,
                        'password' => Hash::make($generatedPassword),
                        'role' => 'client',
                    ]);
                }

                // Б) Создание заказа с привязкой (user_id)
                $order = Order::create([
                    'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                    'user_id' => $user->id,
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'customer_phone' => $normalizedPhone,
                    'shipping_method' => $validated['shipping_method'],
                    'shipping_address' => $validated['shipping_address'],
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                    'promo_code' => $promoCode,
                    'status' => 'new',
                    'payment_method' => 'cod',
                ]);

                // В) Товары и списание стока
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

                // Г) Уведомление в Telegram
                $this->sendTelegramNotification($order);
                
                // Сохраняем ID заказа (может понадобиться)
                session()->flash('last_order_id', $order->id);
            });

            // 4. Очистка корзины ЭТОГО магазина
            session()->forget([$cartKey, $promoKey]);

            // 5. Логика авторизации
            
            // Если мы Админ или Менеджер — НЕ логинимся как клиент, чтобы не потерять админку
            if (Auth::check() && in_array(Auth::user()->role, ['super_admin', 'manager'])) {
                return redirect()->route('home')->with('success', "Order placed successfully. Admin session preserved. Order linked to User ID: {$user->id}");
            }

            // Если мы гость — авторизуем пользователя
            Auth::login($user);

            // Передаем пароль во flash, если он был создан
            if ($generatedPassword) {
                session()->flash('generated_password', $generatedPassword);
                session()->flash('new_account_created', true);
            }

            // 6. Редирект в кабинет
            return redirect()->route('client.profile')->with('success', 'Order placed successfully! Welcome to your profile.');

        } catch (\Exception $e) {
            Log::error("Checkout Error: " . $e->getMessage());
            return back()->with('error', 'Checkout failed: ' . $e->getMessage());
        }
    }

    // --- TELEGRAM ---
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
                       "\nDelivery: {$order->shipping_method}\n" .
                       "Address: {$order->shipping_address}";

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