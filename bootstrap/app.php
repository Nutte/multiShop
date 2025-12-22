<?php
// FILE: bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. Подключаем TenantMiddleware
        // ИСПОЛЬЗУЕМ PREPEND ВМЕСТО APPEND
        // Это важно! Middleware должен запуститься ДО старта сессии (StartSession),
        // чтобы настройка префикса Redis применилась к драйверу сессий.
        $middleware->web(prepend: [
            \App\Http\Middleware\TenantMiddleware::class,
        ]);

        // 2. Если ГОСТЬ пытается зайти в админку -> редирект на Логин
        $middleware->redirectGuestsTo(function (Request $request) {
            return route('admin.login');
        });

        // 3. Если АВТОРИЗОВАННЫЙ пытается открыть Логин -> редирект в Дашборд
        $middleware->redirectUsersTo(function (Request $request) {
            return route('admin.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();