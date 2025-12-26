<?php

//FILE - app/Http/Middleware/SuperAdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяем, авторизован ли пользователь и является ли он супер-админом
        if (auth()->check() && auth()->user()->role !== 'super_admin') {
            abort(403, 'Access denied. Super Admin rights required.');
        }

        return $next($request);
    }
}