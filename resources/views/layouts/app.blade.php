<!-- FILE: resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - @yield('brand_name', 'TriShop')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- ОПРЕДЕЛЕНИЕ ТЕМЫ --}}
    @php
        $tenantId = app(\App\Services\TenantService::class)->getCurrentTenantId();
        
        // Настройки по умолчанию
        $theme = [
            'font' => 'sans-serif',
            'bg-body' => '#f3f4f6',
            'text-main' => '#1f2937',
            'text-muted' => '#9ca3af',
            'color-primary' => '#3b82f6', // Синий
            'color-primary-text' => '#ffffff',
            'color-border' => '#e5e7eb',
            'radius' => '0.5rem',
        ];

        // 1. STREET STYLE (Агрессивный, Темный, Желтый)
        if ($tenantId === 'street_style') {
            $theme = [
                'font' => 'ui-sans-serif, system-ui, sans-serif',
                'bg-body' => '#000000',
                'text-main' => '#ffffff',
                'text-muted' => '#9ca3af',
                'color-primary' => '#facc15', // Желтый
                'color-primary-text' => '#000000',
                'color-border' => '#374151',
                'radius' => '0px', // Острые углы
                'transform' => 'skewX(-10deg)', // Наклон элементов
            ];
        }
        
        // 2. DESIGNER HUB (Минимализм, Светлый, Черно-белый)
        if ($tenantId === 'designer_hub') {
            $theme = [
                'font' => 'ui-serif, Georgia, serif',
                'bg-body' => '#ffffff',
                'text-main' => '#111827',
                'text-muted' => '#6b7280',
                'color-primary' => '#000000', // Черный
                'color-primary-text' => '#ffffff',
                'color-border' => '#e5e7eb',
                'radius' => '0px',
                'transform' => 'none',
            ];
        }

        // 3. MILITARY GEAR (Тактический, Темно-серый, Оранжевый)
        if ($tenantId === 'military_gear') {
            $theme = [
                'font' => 'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace',
                'bg-body' => '#1c1917', // Stone 900
                'text-main' => '#e7e5e4', // Stone 200
                'text-muted' => '#78716c',
                'color-primary' => '#ea580c', // Orange 600
                'color-primary-text' => '#ffffff',
                'color-border' => '#44403c',
                'radius' => '2px',
                'transform' => 'none',
            ];
        }
    @endphp

    <style>
        :root {
            --font-main: {!! $theme['font'] !!};
            --bg-body: {!! $theme['bg-body'] !!};
            --text-main: {!! $theme['text-main'] !!};
            --text-muted: {!! $theme['text-muted'] !!};
            --color-primary: {!! $theme['color-primary'] !!};
            --color-primary-text: {!! $theme['color-primary-text'] !!};
            --color-border: {!! $theme['color-border'] !!};
            --radius: {!! $theme['radius'] !!};
            --transform-style: {!! $theme['transform'] ?? 'none' !!};
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-body);
            color: var(--text-main);
        }

        /* Утилиты для использования переменных в Tailwind */
        .theme-bg { background-color: var(--bg-body); }
        .theme-text { color: var(--text-main); }
        .theme-muted { color: var(--text-muted); }
        
        .theme-btn {
            background-color: var(--color-primary);
            color: var(--color-primary-text);
            border-radius: var(--radius);
            font-weight: bold;
            text-transform: uppercase;
            transition: all 0.3s;
        }
        .theme-btn:hover { opacity: 0.9; transform: scale(1.02); }

        .theme-border { border-color: var(--color-border); }
        .theme-card {
            border: 1px solid var(--color-border);
            background-color: rgba(255, 255, 255, 0.05); /* Легкая прозрачность */
            border-radius: var(--radius);
        }
        
        .theme-input {
            background-color: transparent;
            border: 1px solid var(--color-border);
            color: var(--text-main);
            border-radius: var(--radius);
        }
        .theme-input:focus {
            outline: none;
            border-color: var(--color-primary);
        }
        
        .theme-link {
            color: var(--text-main);
            text-decoration: none;
            border-bottom: 1px solid transparent;
        }
        .theme-link:hover {
            color: var(--color-primary);
            border-bottom-color: var(--color-primary);
        }

        .theme-skew {
            transform: var(--transform-style);
        }
    </style>
</head>
<body class="flex flex-col min-h-screen">
    <!-- Navbar -->
    <nav class="border-b theme-border py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="{{ route('home') }}" class="text-2xl font-bold tracking-widest uppercase">
                @yield('brand_name', 'TriShop')
            </a>
            <div class="flex gap-6 text-sm font-bold uppercase tracking-wider">
                <a href="{{ route('home') }}" class="theme-link">Shop</a>
                @auth
                    <a href="{{ route('client.profile') }}" class="theme-link">Cabinet</a>
                @else
                    <a href="{{ route('client.login') }}" class="theme-link">Login</a>
                @endauth
                <a href="{{ route('cart.index') }}" class="theme-link flex items-center gap-1">
                    Cart
                    @php
                        $tId = app(\App\Services\TenantService::class)->getCurrentTenantId();
                        $count = count(session("cart_{$tId}", []));
                    @endphp
                    @if($count > 0)
                        <span style="background-color: var(--color-primary); color: var(--color-primary-text);" class="text-[10px] rounded-full w-5 h-5 flex items-center justify-center">
                            {{ $count }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow container mx-auto mt-8 px-4">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 mb-6 border theme-border" style="color: #22c55e;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-6 border theme-border" style="color: #ef4444;">
                Error: {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-12 py-8 border-t theme-border text-center text-sm theme-muted">
        &copy; {{ date('Y') }} @yield('brand_name'). All rights reserved.
    </footer>
</body>
</html>