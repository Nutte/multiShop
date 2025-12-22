    <!-- FILE: resources/views/layouts/app.blade.php -->
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title')</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="@yield('body_class', 'bg-gray-100')">
        <nav class="p-4 text-white @yield('nav_class', 'bg-gray-800')">
            <div class="container mx-auto flex justify-between items-center">
                <a href="/" class="text-2xl font-bold">@yield('brand_name', 'TriShop')</a>
                <div>
                    <a href="{{ route('cart.index') }}" class="hover:underline">Cart</a>
                </div>
            </div>
        </nav>

        <div class="container mx-auto mt-8 p-4">
            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
            @endif
            @yield('content')
        </div>
    </body>
    </html>