<?php

// FILE: routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\TelegramSettingsController;
use App\Http\Controllers\Admin\ClothingLineController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Middleware\AdminTenantMiddleware;

// --- АДМИНКА ---
Route::domain(config('tenants.admin_domain'))->group(function () {
    
    Route::get('/', function () { return redirect()->route('admin.dashboard'); });

    Route::middleware('guest')->prefix('admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Группа маршрутов с префиксом 'admin' и именем 'admin.'
    Route::middleware(['auth', AdminTenantMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // Tenant Switching
        Route::post('/switch-tenant', function (\Illuminate\Http\Request $request) {
            if (auth()->user()->role !== 'super_admin') abort(403);
            if ($request->tenant_id === 'root') session()->forget('admin_current_tenant_id');
            else session()->put('admin_current_tenant_id', $request->tenant_id);
            return back();
        })->name('switch_tenant');

        // Resources
        Route::resource('users', UserController::class);
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);
        Route::post('/orders/{id}/notify', [OrderController::class, 'sendNotification'])->name('orders.notify');
        
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        
        Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
        Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
        Route::delete('/attributes/{id}', [AttributeController::class, 'destroy'])->name('attributes.destroy');

        Route::resource('clothing-lines', ClothingLineController::class);
        
        // Promo Codes
        Route::resource('promocodes', PromoCodeController::class)->only(['index', 'create', 'store', 'destroy']);

        // Settings & Telegram
        Route::prefix('settings/telegram')->name('settings.telegram.')->group(function () {
            Route::get('/', [TelegramSettingsController::class, 'index'])->name('index');
            Route::post('/', [TelegramSettingsController::class, 'store'])->name('store');
            Route::put('/{id}', [TelegramSettingsController::class, 'update'])->name('update');
            Route::delete('/{id}', [TelegramSettingsController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/test', [TelegramSettingsController::class, 'test'])->name('test');
            Route::post('/send-message', [TelegramSettingsController::class, 'sendMessage'])->name('sendMessage');
        });

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        
        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

        // --- INVENTORY ROUTES ---
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        // НОВЫЙ МАРШРУТ: Отправка отчета в Telegram
        Route::post('/inventory/send-telegram', [InventoryController::class, 'sendToTelegram'])->name('inventory.send_telegram');
    });
});

// --- МАГАЗИНЫ ---
Route::group([], function () {
    Route::get('/', [ShopController::class, 'index'])->name('home');
    Route::get('/products/{slug}', [ShopController::class, 'show'])->name('product.show');
    // КОРЗИНА И ЗАКАЗ
    Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [\App\Http\Controllers\CartController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/remove/{rowId}', [\App\Http\Controllers\CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/promo', [\App\Http\Controllers\CartController::class, 'applyPromo'])->name('cart.promo');
    Route::post('/checkout', [\App\Http\Controllers\CartController::class, 'checkout'])->name('checkout');
});