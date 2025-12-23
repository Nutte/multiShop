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

// --- АДМИНКА ---
Route::domain(config('tenants.admin_domain'))->group(function () {
    
    Route::get('/', function () { return redirect()->route('admin.dashboard'); });

    Route::middleware('guest')->prefix('admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::post('/switch-tenant', function (\Illuminate\Http\Request $request) {
            if (auth()->user()->role !== 'super_admin') abort(403);
            if ($request->tenant_id === 'root') session()->forget('admin_current_tenant_id');
            else session()->put('admin_current_tenant_id', $request->tenant_id);
            return back();
        })->name('switch_tenant');

        // USERS: Доступен только Super Admin (проверка внутри контроллера)
        Route::resource('users', UserController::class);
        
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'update']);
        Route::post('/orders/{id}/notify', [OrderController::class, 'sendNotification'])->name('orders.notify');
        
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class); // Использует стандартный {category} -> id
        
        // ATTRIBUTES: Ручные маршруты для точности
        Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
        Route::post('/attributes', [AttributeController::class, 'store'])->name('attributes.store');
        // ВАЖНО: Явно указываем {id}
        Route::delete('/attributes/{id}', [AttributeController::class, 'destroy'])->name('attributes.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});

// --- МАГАЗИНЫ ---
Route::group([], function () {
    Route::get('/', [ShopController::class, 'index'])->name('home');
    Route::get('/products/{slug}', [ShopController::class, 'show'])->name('product.show');
    Route::get('/cart', [ShopController::class, 'cart'])->name('cart.index');
    Route::post('/cart/add', [ShopController::class, 'addToCart'])->name('cart.add');
    Route::post('/checkout', [ShopController::class, 'checkout'])->name('checkout');
});