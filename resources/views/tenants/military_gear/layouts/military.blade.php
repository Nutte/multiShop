<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'KARAKURT | Tactical Art Wear')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@400;600;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        military: {
                            black: '#09090b',
                            dark: '#18181b',
                            gray: '#27272a',
                            text: '#a1a1aa',
                            light: '#e4e4e7',
                            accent: '#ea580c',
                        }
                    },
                    fontFamily: {
                        sans: ['"Chakra Petch"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    backgroundImage: {
                        'grid-pattern': "linear-gradient(to right, #27272a 1px, transparent 1px), linear-gradient(to bottom, #27272a 1px, transparent 1px)",
                    },
                    screens: {
                        'print': {'raw': 'print'},
                    },
                    animation: {
                        'glitch': 'glitch 1s linear infinite',
                    },
                    keyframes: {
                        glitch: {
                            '2%, 64%': { transform: 'translate(2px,0) skew(0deg)' },
                            '4%, 60%': { transform: 'translate(-2px,0) skew(0deg)' },
                            '62%': { transform: 'translate(0,0) skew(5deg)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #050505;
            color: #e4e4e7;
        }
        .tech-border {
            position: relative;
            border: 1px solid #27272a;
        }
        .corner-accent::before {
            content: '';
            position: absolute;
            top: -1px; left: -1px;
            width: 8px; height: 8px;
            border-top: 2px solid #ea580c;
            border-left: 2px solid #ea580c;
        }
        .corner-accent::after {
            content: '';
            position: absolute;
            bottom: -1px; right: -1px;
            width: 8px; height: 8px;
            border-bottom: 2px solid #ea580c;
            border-right: 2px solid #ea580c;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #09090b;
        }
        ::-webkit-scrollbar-thumb {
            background: #27272a;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #ea580c;
        }
        
        /* Checkbox Style */
        .tactical-checkbox:checked {
            background-color: #ea580c;
            border-color: #ea580c;
            background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='black' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
        }

        /* PRINT STYLES */
        @media print {
            body {
                background-color: white !important;
                color: black !important;
            }
            header, footer, .no-print {
                display: none !important;
            }
            .tech-border {
                border: 2px solid black !important;
            }
            .text-white, .text-military-text {
                color: black !important;
            }
            .bg-military-dark, .bg-military-black {
                background-color: transparent !important;
            }
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col font-sans selection:bg-military-accent selection:text-white">

    <!-- MOBILE MENU OVERLAY -->
    <div id="mobile-menu" class="fixed inset-0 z-[100] bg-military-black/95 backdrop-blur-xl hidden flex-col transition-all duration-300 transform translate-x-full">
        <!-- Close Button -->
        <div class="absolute top-4 right-4">
            <button onclick="toggleMobileMenu()" class="p-2 border border-military-gray text-white hover:border-military-accent hover:text-military-accent transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <!-- Menu Links -->
        <div class="flex-grow flex flex-col justify-center px-8 space-y-6">
            <div class="border-l-2 border-military-accent pl-6">
                <span class="text-xs font-mono text-military-text block mb-2">/// NAVIGATION</span>
                <a href="{{ route('home') }}" onclick="toggleMobileMenu()" class="block text-4xl font-bold uppercase text-white hover:text-military-accent transition-colors mb-4">Base</a>
                <a href="{{ route('shop.products') }}" onclick="toggleMobileMenu()" class="block text-4xl font-bold uppercase text-white hover:text-military-accent transition-colors mb-4">Armory (Catalog)</a>
                @auth
                <a href="{{ route('client.profile') }}" onclick="toggleMobileMenu()" class="block text-4xl font-bold uppercase text-white hover:text-military-accent transition-colors mb-4">Operator Data</a>
                @else
                <a href="{{ route('client.login') }}" onclick="toggleMobileMenu()" class="block text-4xl font-bold uppercase text-white hover:text-military-accent transition-colors mb-4">Operator Data</a>
                @endauth
                <a href="{{ route('contact.index') }}" onclick="toggleMobileMenu()" class="block text-4xl font-bold uppercase text-white hover:text-military-accent transition-colors">Comms</a>
            </div>
            
            <div class="border-t border-military-gray pt-6 mt-6">
                <span class="text-xs font-mono text-military-text block mb-4">/// QUICK ACCESS</span>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('cart.index') }}" onclick="toggleMobileMenu()" class="border border-military-gray py-4 text-white font-mono uppercase text-sm hover:bg-military-accent hover:text-black hover:border-military-accent transition-colors flex items-center justify-center">
                        Supply Crate [<span id="mobile-cart-count">{{ count(session('cart', [])) }}</span>]
                    </a>
                    @auth
                    <a href="{{ route('client.profile') }}" onclick="toggleMobileMenu()" class="border border-military-gray py-4 text-white font-mono uppercase text-sm hover:bg-white hover:text-black hover:border-white transition-colors flex items-center justify-center">
                        {{ strtoupper(substr(auth()->user()->name, 0, 3)) }}
                    </a>
                    @else
                    <a href="{{ route('client.login') }}" onclick="toggleMobileMenu()" class="border border-military-gray py-4 text-white font-mono uppercase text-sm hover:bg-white hover:text-black hover:border-white transition-colors flex items-center justify-center">
                        Login / ID
                    </a>
                    @endauth
                </div>
            </div>
        </div>
        
        <!-- Decor Footer -->
        <div class="p-8 text-[10px] font-mono text-zinc-600 uppercase flex justify-between">
            <span>SYS.VER.2.0.4</span>
            <span>SECURE CONN</span>
        </div>
    </div>

    <!-- NAVIGATION -->
    <header class="fixed w-full z-50 bg-military-black/90 backdrop-blur-md border-b border-military-gray no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <button type="button" onclick="toggleMobileMenu()" class="text-military-text hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center gap-2">
                    <div class="w-8 h-8 bg-military-accent flex items-center justify-center font-bold text-black text-xl">K</div>
                    <span class="font-bold text-2xl tracking-widest text-white uppercase">Karakurt<span class="text-military-accent text-xs align-top">.UA</span></span>
                </a>

                <!-- Desktop Nav -->
                <nav class="hidden md:flex space-x-8">
                    <a href="{{ route('home') }}" class="text-sm font-mono uppercase tracking-wider {{ request()->routeIs('home') ? 'text-military-accent' : 'text-military-text' }} hover:text-military-accent transition-colors">Base</a>
                    <a href="{{ route('shop.products') }}" class="text-sm font-mono uppercase tracking-wider {{ request()->routeIs('shop.products') ? 'text-military-accent' : 'text-military-text' }} hover:text-military-accent transition-colors">Armory</a>
                    <a href="{{ route('contact.index') }}" class="text-sm font-mono uppercase tracking-wider {{ request()->routeIs('contact.*') ? 'text-military-accent' : 'text-military-text' }} hover:text-military-accent transition-colors">Comms</a>
                </nav>

                <!-- Icons -->
                <div class="flex items-center space-x-6">
                    <button class="text-military-text hover:text-white transition-colors relative group">
                        <span class="absolute -top-1 -right-1 w-2 h-2 bg-military-accent rounded-full animate-pulse"></span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                    <!-- Cart -->
                    <a href="{{ route('cart.index') }}" class="text-military-text hover:text-white transition-colors relative">
                        <span class="absolute -top-2 -right-2 bg-military-gray border border-military-accent text-military-accent text-[10px] font-mono px-1">{{ count(session('cart', [])) }}</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </a>
                    <!-- Auth/Profile Link -->
                    @auth
                    <a href="{{ route('client.profile') }}" class="hidden md:block text-military-text hover:text-white font-mono text-xs uppercase border border-military-gray px-3 py-1 hover:border-military-accent transition-all group">
                        <span class="group-hover:hidden">{{ strtoupper(substr(auth()->user()->name, 0, 3)) }}</span>
                        <span class="hidden group-hover:inline text-military-accent">PROFILE</span>
                    </a>
                    @else
                    <a href="{{ route('client.login') }}" class="hidden md:block text-military-text hover:text-white font-mono text-xs uppercase border border-military-gray px-3 py-1 hover:border-military-accent transition-all group">
                        <span class="group-hover:hidden">ACCESS</span>
                        <span class="hidden group-hover:inline text-military-accent">LOGIN</span>
                    </a>
                    @endauth
                </div>
            </div>
        </div>
        <!-- Decoration Line -->
        <div class="h-[1px] w-full bg-military-accent/20 flex justify-between">
            <div class="w-1/4 h-full bg-military-accent/60"></div>
            <div class="w-[10px] h-full bg-military-accent"></div>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="flex-grow pt-20">
        @if(session('success'))
        <div class="fixed top-24 right-4 z-50 bg-green-900 border border-green-700 text-green-300 px-4 py-2 rounded text-sm font-mono">
            {{ session('success') }}
        </div>
        @endif
        
        @if(session('error'))
        <div class="fixed top-24 right-4 z-50 bg-red-900 border border-red-700 text-red-300 px-4 py-2 rounded text-sm font-mono">
            {{ session('error') }}
        </div>
        @endif
        
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-military-black border-t border-military-gray pt-16 pb-8 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-6 h-6 bg-military-accent flex items-center justify-center font-bold text-black text-xs">K</div>
                        <span class="font-bold text-xl tracking-widest text-white uppercase">Karakurt</span>
                    </div>
                    <p class="text-military-text text-sm mb-4">Tactical inspired streetwear designed in Ukraine. Est. 2024.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-military-text hover:text-white transition-colors">INST</a>
                        <a href="#" class="text-military-text hover:text-white transition-colors">TLG</a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-white font-bold uppercase mb-4 text-sm">Навигация</h4>
                    <ul class="space-y-2 text-sm text-military-text">
                        <li><a href="{{ route('home') }}" class="hover:text-military-accent transition-colors">> Главная</a></li>
                        <li><a href="{{ route('shop.products') }}" class="hover:text-military-accent transition-colors">> Каталог</a></li>
                        <li><a href="{{ route('contact.index') }}" class="hover:text-military-accent transition-colors">> Контакты</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold uppercase mb-4 text-sm">Клиентам</h4>
                    <ul class="space-y-2 text-sm text-military-text">
                        <li><a href="#" class="hover:text-military-accent transition-colors">> Доставка и оплата</a></li>
                        <li><a href="#" class="hover:text-military-accent transition-colors">> Обмен и возврат</a></li>
                        <li><a href="#" class="hover:text-military-accent transition-colors">> Таблица размеров</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold uppercase mb-4 text-sm">Рассылка</h4>
                    <form class="flex border border-military-gray focus-within:border-military-accent transition-colors">
                        <input type="email" placeholder="EMAIL ADDRESS" class="bg-transparent text-white text-sm px-4 py-2 w-full focus:outline-none font-mono placeholder-zinc-600">
                        <button class="bg-military-gray text-white px-4 hover:bg-military-accent hover:text-black transition-colors">-></button>
                    </form>
                </div>
            </div>
            
            <div class="border-t border-military-gray pt-8 flex flex-col md:flex-row justify-between items-center text-[10px] font-mono text-zinc-600 uppercase">
                <p>&copy; 2024 KARAKURT.UA. ALL RIGHTS RESERVED.</p>
                <p>DESIGNED BY [AI_ARCHITECT]</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                menu.classList.add('flex');
                setTimeout(() => {
                    menu.classList.remove('translate-x-full');
                }, 10);
            } else {
                menu.classList.add('translate-x-full');
                setTimeout(() => {
                    menu.classList.add('hidden');
                    menu.classList.remove('flex');
                }, 300);
            }
        }
    </script>
</body>
</html>