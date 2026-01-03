<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\Admin\OrderExportController;
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
use App\Http\Controllers\Admin\ManagerController; 
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Middleware\AdminTenantMiddleware;
use App\Http\Middleware\SuperAdminMiddleware; 
use App\Http\Middleware\CheckOrderTenantAccess;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Client\AuthController as ClientAuthController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\ContactController;

// --- АДМИН ПАНЕЛЬ ---
Route::domain(config('tenants.admin_domain'))->group(function () {
    Route::get('/', function () { return redirect()->route('admin.dashboard'); });
    Route::middleware('guest')->prefix('admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth', AdminTenantMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::post('/switch-tenant', function (\Illuminate\Http\Request $request) {
            if (auth()->user()->role !== 'super_admin') abort(403);
            if ($request->tenant_id === 'root') session()->forget('admin_current_tenant_id');
            else session()->put('admin_current_tenant_id', $request->tenant_id);
            return back();
        })->name('switch_tenant');

        Route::resource('users', UserController::class);
        
        Route::resource('managers', ManagerController::class)
            ->middleware(SuperAdminMiddleware::class);

        // Используем новые контроллеры для заказов
        Route::prefix('orders')->name('orders.')->middleware(CheckOrderTenantAccess::class)->group(function () {
            // Основные CRUD операции
            Route::get('/', [OrderManagementController::class, 'index'])->name('index');
            Route::get('/create', [OrderManagementController::class, 'create'])->name('create');
            Route::post('/', [OrderManagementController::class, 'store'])->name('store');
            Route::get('/{id}', [OrderManagementController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [OrderManagementController::class, 'edit'])->name('edit');
            Route::put('/{id}', [OrderManagementController::class, 'update'])->name('update');
            
            // Старый маршрут для уведомлений (оставляем для обратной совместимости)
            Route::post('/{id}/notify', [OrderManagementController::class, 'sendNotification'])->name('notify');
        });

        Route::resource('messages', ContactMessageController::class)->only(['index', 'show', 'destroy']);

        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
        Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
        Route::delete('/attributes/{id}', [AttributeController::class, 'destroy'])->name('attributes.destroy');
        Route::resource('clothing-lines', ClothingLineController::class);
        Route::resource('promocodes', PromoCodeController::class)->only(['index', 'create', 'store', 'destroy']);
        
        Route::prefix('settings/telegram')->name('settings.telegram.')->group(function () {
            Route::get('/', [TelegramSettingsController::class, 'index'])->name('index');
            Route::post('/', [TelegramSettingsController::class, 'store'])->name('store');
            Route::put('/{id}', [TelegramSettingsController::class, 'update'])->name('update');
            Route::delete('/{id}', [TelegramSettingsController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/test', [TelegramSettingsController::class, 'test'])->name('test');
            Route::post('/send-message', [TelegramSettingsController::class, 'sendMessage'])->name('sendMessage');
        });

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/settings/content', [SettingsController::class, 'content'])->name('settings.content');
        Route::post('/settings/content/update', [SettingsController::class, 'updateContent'])->name('settings.content.update');
        Route::post('/settings/content/delete', [SettingsController::class, 'deleteContent'])->name('settings.content.delete');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/send-telegram', [InventoryController::class, 'sendToTelegram'])->name('inventory.send_telegram');

        // Маршруты для управления статусами заказов
        Route::prefix('order-status')->name('order-status.')->group(function () {
            Route::post('/{id}/quick-update', [OrderStatusController::class, 'quickUpdate'])->name('quick-update');
            Route::get('/statistics', [OrderStatusController::class, 'statistics'])->name('statistics');
            Route::post('/bulk-update', [OrderStatusController::class, 'bulkUpdate'])->name('bulk-update');
        });

        // Маршруты для экспорта заказов
        Route::prefix('order-export')->name('order-export.')->group(function () {
            Route::get('/csv', [OrderExportController::class, 'exportCsv'])->name('csv');
            Route::get('/{id}/detail', [OrderExportController::class, 'exportOrderDetail'])->name('detail');
            Route::get('/period-report', [OrderExportController::class, 'periodReport'])->name('period-report');
        });
    });
});

// --- МАГАЗИНЫ (КЛИЕНТСКАЯ ЧАСТЬ) ---
Route::group([], function () {
    
    // Auth
    Route::get('/login', [ClientAuthController::class, 'showLogin'])->name('client.login');
    Route::post('/login', [ClientAuthController::class, 'login']);
    Route::post('/logout', [ClientAuthController::class, 'logout'])->name('client.logout');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/remove/{rowId}', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/promo', [CartController::class, 'applyPromo'])->name('cart.promo');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');

    // Profile
    Route::middleware('auth')->group(function () {
        Route::get('/user-profile', [ProfileController::class, 'index'])->name('client.profile');
        Route::get('/user-profile/orders/{id}', [ProfileController::class, 'showOrder'])->name('client.orders.show');
        Route::post('/user-profile/password', [ClientAuthController::class, 'updatePassword'])->name('client.password.update');
    });

    // Contact
    Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

    // Main & Products
    Route::get('/', [ShopController::class, 'index'])->name('home');
    Route::get('/products/{slug}', [ShopController::class, 'show'])->name('product.show');

    // FILE: routes/web.php
    /*
    Route::get('/test-content', function() {
        echo "<h1>Тест системы контента</h1>";  
        
        // Проверяем
        echo "<h2>1. Тест ContentHelper::has():</h2>";
        echo ContentHelper::has('test_header') ? '✅ Блок найден' : '❌ Блок не найден';
        
        echo "<h2>2. Тест ContentHelper::text():</h2>";
        echo ContentHelper::text('test_header', 'Нет контента');
        
        echo "<h2>3. Тест ContentHelper::renderText():</h2>";
        echo ContentHelper::renderText('test_header');
        
        echo "<h2>4. Все блоки:</h2>";
        echo '<pre>';
        print_r(ContentHelper::all());
        echo '</pre>';
    });
    */
});