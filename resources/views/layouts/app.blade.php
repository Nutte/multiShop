<!-- FILE: resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - @yield('brand_name', 'TriShop')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="@yield('body_class', 'bg-gray-100 text-gray-800')">
    <nav class="p-4 text-white shadow-md @yield('nav_class', 'bg-gray-900')">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-2xl font-bold tracking-widest">@yield('brand_name', 'TriShop')</a>
            <div class="flex gap-6 text-sm font-bold uppercase tracking-wider">
                <a href="{{ route('home') }}" class="hover:text-gray-300 transition">Shop</a>
                <a href="{{ route('cart.index') }}" class="hover:text-gray-300 transition flex items-center gap-1">
                    Cart
                    <!-- Счетчик товаров (опционально) -->
                    @php
                        $tenantId = app(\App\Services\TenantService::class)->getCurrentTenantId();
                        $count = count(session("cart_{$tenantId}", []));
                    @endphp
                    @if($count > 0)
                        <span class="bg-red-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center">{{ $count }}</span>
                    @endif
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto mt-8 p-4 min-h-[60vh]">
        <!-- ОТОБРАЖЕНИЕ СООБЩЕНИЙ ОБ УСПЕХЕ -->
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- ОТОБРАЖЕНИЕ ОШИБОК (Критично для понимания почему заказ не прошел) -->
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm">
                <strong>Error:</strong> {{ session('error') }}
            </div>
        @endif

        <!-- ОШИБКИ ВАЛИДАЦИИ ФОРМ -->
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 shadow-sm">
                <div class="font-bold text-red-700 mb-1">Please check the form:</div>
                <ul class="list-disc pl-5 text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="bg-gray-900 text-white py-8 mt-12">
        <div class="container mx-auto text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} @yield('brand_name', 'TriShop'). All rights reserved.
        </div>
    </footer>
</body>
</html>