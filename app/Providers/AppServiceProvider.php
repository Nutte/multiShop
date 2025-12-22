<?php
// FILE: app/Providers/AppServiceProvider.php

namespace App\Providers;

use App\Services\TenantService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ВАЖНО: Регистрируем TenantService как Singleton.
        // Это гарантирует, что данные, установленные в Middleware,
        // будут доступны в Контроллере.
        $this->app->singleton(TenantService::class, function ($app) {
            return new TenantService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}