<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'GADYUKA.BRAND')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Permanent+Marker&family=Space+Grotesk:wght@500;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Space Grotesk', 'sans-serif'],
                        marker: ['Permanent Marker', 'cursive'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            accent: '#ff003c',
                            gray: '#333333'
                        }
                    },
                    boxShadow: {
                        'sharp': '4px 4px 0px 0px #ffffff',
                        'sharp-red': '4px 4px 0px 0px #ff003c',
                    },
                    animation: {
                        'marquee': 'marquee 20s linear infinite',
                    },
                    keyframes: {
                        marquee: {
                            '0%': { transform: 'translateX(0%)' },
                            '100%': { transform: 'translateX(-50%)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        
        /* Manga Dark Theme */
        body {
            background-color: #050505;
            color: #ffffff;
            background-image: radial-gradient(circle, #222 1px, transparent 1.5px);
            background-size: 8px 8px;
        }

        .img-manga {
            filter: grayscale(100%) contrast(120%) brightness(90%);
            mix-blend-mode: luminosity;
            transition: filter 0.3s;
        }
        .group:hover .img-manga {
            filter: grayscale(0%) contrast(110%);
            mix-blend-mode: normal;
        }

        .glitch-hover:hover {
            animation: glitch 0.3s cubic-bezier(.25, .46, .45, .94) both infinite;
            color: #ff003c;
        }
        @keyframes glitch {
            0% { transform: translate(0) }
            20% { transform: translate(-2px, 2px) }
            40% { transform: translate(-2px, -2px) }
            60% { transform: translate(2px, 2px) }
            80% { transform: translate(2px, -2px) }
            100% { transform: translate(0) }
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
        }
    </style>
</head>
<body class="antialiased">

    <!-- HEADER -->
    <header class="fixed top-0 w-full z-40 bg-black/90 backdrop-blur-md border-b border-white/20 no-print">
        <!-- Scrolling Tape -->
        <div class="bg-white text-black py-0.5 overflow-hidden border-b border-white">
            <div class="whitespace-nowrap animate-marquee flex gap-8 font-mono text-[10px] uppercase font-bold tracking-widest">
                <span>/// SYSTEM_OVERRIDE /// DARK_MODE_ENGAGED /// PROJECT: GADYUKA /// 警告: DO NOT TOUCH ///</span>
                <span>/// SYSTEM_OVERRIDE /// DARK_MODE_ENGAGED /// PROJECT: GADYUKA /// 警告: DO NOT TOUCH ///</span>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between relative overflow-hidden">
            <!-- Mobile Menu Button -->
            <button class="md:hidden p-2 text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Logo -->
            <a href="{{ route('home') }}" class="text-2xl md:text-3xl font-display font-bold text-white cursor-pointer tracking-tighter flex items-center gap-2 group">
                <span class="text-brand-accent font-marker text-4xl group-hover:scale-110 transition-transform">G</span>
                <div class="flex flex-col leading-none">
                    <span>GADYUKA</span>
                    <span class="text-[8px] font-mono text-brand-accent tracking-[0.3em]">ガデュカ・ブランド</span>
                </div>
            </a>

            <!-- Navigation -->
            <nav class="hidden md:flex gap-8 text-xs font-mono font-bold uppercase tracking-widest">
                <a href="{{ route('home') }}" class="hover:text-brand-accent hover:underline decoration-2 underline-offset-4 transition-all">Collection_01</a>
                <a href="{{ route('home') }}" class="hover:text-brand-accent hover:underline decoration-2 underline-offset-4 transition-all">Manga_Archive</a>
                <a href="{{ route('contact.index') }}" class="hover:text-brand-accent hover:underline decoration-2 underline-offset-4 transition-all">Signal</a>
            </nav>

            <!-- Icons -->
            <div class="flex items-center gap-6">
                @auth
                    <a href="{{ route('client.profile') }}" class="hidden md:block hover:text-brand-accent transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path>
                            <circle cx="12" cy="7" r="4" stroke-width="2"></circle>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('client.login') }}" class="hidden md:block hover:text-brand-accent transition-colors text-xs font-mono uppercase">Login</a>
                @endauth
                
                <a href="{{ route('cart.index') }}" class="relative hover:text-brand-accent transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    @if(isset($cartCount) && $cartCount > 0)
                        <span class="absolute -top-1 -right-2 w-4 h-4 bg-brand-accent text-white flex items-center justify-center text-[9px] font-bold border border-black">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="pt-24 min-h-screen pb-12">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-black text-white py-12 border-t-2 border-white relative overflow-hidden no-print">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h2 class="font-display font-bold text-4xl mb-6 tracking-tighter">GADYUKA.BRAND</h2>
            <div class="flex justify-center gap-8 font-mono font-bold text-xs uppercase mb-8">
                <a href="#" class="hover:text-brand-accent hover:underline">[Instagram]</a>
                <a href="#" class="hover:text-brand-accent hover:underline">[Telegram]</a>
                <a href="#" class="hover:text-brand-accent hover:underline">[DarkNet]</a>
            </div>
            <p class="font-mono text-[10px] text-gray-600">© {{ date('Y') }} SYSTEM ONLINE. ALL RIGHTS RESERVED.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>