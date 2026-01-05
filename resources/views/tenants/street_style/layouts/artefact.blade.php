<!-- FILE: resources/views/tenants/street_style/layouts/artefact.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ARTEFACT.ROOM // STREET_MOD')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=Syne:wght@400;700;800&family=Michroma&family=Reenie+Beanie&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">

    <style>
        /* --- STREET VISUAL DNA --- */
        :root {
            --c-void: #111111;       /* Asphalt Black */
            --c-concrete: #2a2a2a;   /* Wall Grey */
            --c-paper: #f2f0e9;      /* Wheatpaste Paper */
            --c-spray-neon: #ccff00; /* Acid Green */
            --c-spray-pink: #ff0099; /* Punk Pink */
            --c-tape: #ffcc00;       /* Caution Yellow */
        }

        body {
            background-color: var(--c-void);
            color: var(--c-paper);
            font-family: 'Space Mono', monospace;
            /* Concrete Noise Texture */
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.08'/%3E%3C/svg%3E");
        }

        /* Typography */
        .font-display { font-family: 'Syne', sans-serif; }
        .font-spray { font-family: 'Permanent Marker', cursive; transform: rotate(-2deg); }
        .font-tech { font-family: 'Space Mono', monospace; }
        .font-sketch { font-family: 'Reenie Beanie', cursive; font-weight: 500; }
        .font-receipt { font-family: 'Courier Prime', monospace; }

        /* UI COMPONENTS (из index.html) */
        .header-glass {
            background: rgba(17, 17, 17, 0.85);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .poster-card {
            background-color: var(--c-paper);
            color: black;
            position: relative;
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
            transition: all 0.3s ease;
        }
        .poster-card:hover {
            transform: translateY(-5px) rotate(1deg);
            box-shadow: 0 15px 30px rgba(0,0,0,0.7), 0 0 0 2px var(--c-spray-neon);
            z-index: 10;
        }
        
        .filter-btn {
            border: 1px solid #333;
            color: #666;
            transition: 0.2s;
        }
        .filter-btn:hover, .filter-btn.active {
            border-color: var(--c-spray-neon);
            color: var(--c-spray-neon);
            background: rgba(204, 255, 0, 0.05);
        }
        
        .check-box {
            width: 16px;
            height: 16px;
            border: 1px solid #666;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
        }
        input:checked + .check-box {
            background: var(--c-spray-neon);
            border-color: var(--c-spray-neon);
        }
        input:checked + .check-box::after {
            content: 'X';
            color: black;
            font-size: 10px;
            font-weight: bold;
        }
        
        .tape-strip {
            position: absolute;
            background-color: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(2px);
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
            z-index: 20;
        }
        
        .text-neon { color: var(--c-spray-neon); }
        .bg-neon { background-color: var(--c-spray-neon); }
        .border-neon { border-color: var(--c-spray-neon); }
        .stroke-text { -webkit-text-stroke: 1px white; color: transparent; }
    </style>
</head>
<body class="antialiased min-h-screen relative selection:bg-pink-500 selection:text-white overflow-x-hidden">

    <!-- PREVIEW CONTROL -->
    <div class="fixed top-0 left-0 w-full bg-black z-50 border-b border-gray-800 p-2 flex flex-wrap gap-2 justify-between items-center text-xs font-mono text-gray-500">
        <div class="flex gap-4">
            <span class="font-bold text-white">ARTEFACT.STREET_MODE</span>
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'nav-tab-active' : '' }} hover:text-white">HOME</a>
            <a href="{{ route('contact.index') }}" class="{{ request()->routeIs('contact.*') ? 'nav-tab-active' : '' }} hover:text-white">CONTACT</a>
            <a href="{{ route('cart.index') }}" class="{{ request()->routeIs('cart.*') ? 'nav-tab-active' : '' }} hover:text-white">CART</a>
            @auth
                <a href="{{ route('client.profile') }}" class="{{ request()->routeIs('client.profile') ? 'nav-tab-active' : '' }} hover:text-white">PROFILE</a>
            @else
                <a href="{{ route('client.login') }}" class="{{ request()->routeIs('client.login') ? 'nav-tab-active' : '' }} hover:text-white">LOGIN</a>
            @endauth
        </div>
    </div>
    
    <!-- HEADER -->
    <header class="w-full fixed top-10 left-0 z-40 header-glass">
        <div class="w-full px-6 h-20 flex items-center justify-between">
            <a href="{{ route('home') }}" class="relative group">
                <span class="text-4xl font-display font-black tracking-tighter uppercase italic">Artefact</span>
                <span class="absolute -bottom-2 right-0 font-spray text-xl text-pink-500 -rotate-6">System</span>
            </a>
            <div class="flex items-center gap-6 font-tech text-xs">
                <a href="{{ route('cart.index') }}" class="border border-white bg-black px-4 py-1 hover:bg-white hover:text-black transition uppercase">
                    Cart [
                    @php
                        $tenantId = app(\App\Services\TenantService::class)->getCurrentTenantId();
                        $count = count(session("cart_{$tenantId}", []));
                    @endphp
                    {{ $count }}
                    ]
                </a>
                @auth
                    <a href="{{ route('client.profile') }}" class="hover:text-pink-500">PROFILE</a>
                @else
                    <a href="{{ route('client.login') }}" class="hover:text-pink-500">LOGIN</a>
                @endauth
            </div>
        </div>
    </header>
    <div class="h-10"></div>

    <!-- Main Content -->
    <main class="flex-grow">
        @if(session('success'))
            <div class="fixed top-20 right-6 z-50 bg-green-500 text-white px-6 py-3 rounded shadow-lg">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="fixed top-20 right-6 z-50 bg-red-500 text-white px-6 py-3 rounded shadow-lg">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="border-t-4 border-white bg-black pt-16 pb-8 px-6 mt-0 relative overflow-hidden">
        <!-- Background Graffiti -->
        <div class="absolute top-10 right-10 opacity-20 pointer-events-none">
            <span class="font-spray text-[10rem] text-white rotate-12 block leading-none">End.</span>
        </div>

        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-12 relative z-10">
            <!-- Brand -->
            <div class="space-y-4">
                <h3 class="font-display font-black text-3xl uppercase italic text-white">Artefact<span class="text-[#ccff00] not-italic">.Room</span></h3>
                <p class="font-tech text-xs text-gray-400 leading-relaxed">
                    Цифровой бутик уличной моды.<br>
                    Основан в Киеве, Украина.<br>
                    Est. 2025.
                </p>
            </div>

            <!-- Navigation -->
            <div>
                <h4 class="font-display font-bold text-xl uppercase text-white mb-6">Navigation</h4>
                <ul class="space-y-2 font-tech text-sm text-gray-400">
                    <li><a href="{{ route('home') }}" class="hover:text-[#ccff00] hover:underline decoration-wavy">Home Base</a></li>
                    <li><a href="{{ route('cart.index') }}" class="hover:text-[#ccff00] hover:underline decoration-wavy">Cart / Checkout</a></li>
                    @auth
                        <li><a href="{{ route('client.profile') }}" class="hover:text-[#ccff00] hover:underline decoration-wavy">Profile Access</a></li>
                    @else
                        <li><a href="{{ route('client.login') }}" class="hover:text-[#ccff00] hover:underline decoration-wavy">Login</a></li>
                    @endauth
                </ul>
            </div>

            <!-- Contacts -->
            <div>
                <h4 class="font-display font-bold text-xl uppercase text-white mb-6">Comms</h4>
                <ul class="space-y-4 font-tech text-sm text-gray-400">
                    <li>
                        <span class="block text-[10px] text-gray-600 uppercase font-bold">Email_</span>
                        <a href="mailto:hello@artefact.ua" class="hover:text-white">hello@artefact.ua</a>
                    </li>
                    <li>
                        <span class="block text-[10px] text-gray-600 uppercase font-bold">Phone_</span>
                        <a href="tel:+380440000000" class="hover:text-white">+380 44 000 0000</a>
                    </li>
                    <li>
                        <span class="block text-[10px] text-gray-600 uppercase font-bold">HQ_</span>
                        Volodymyrska St, Kyiv, UA
                    </li>
                </ul>
            </div>

            <!-- Socials -->
            <div>
                <h4 class="font-display font-bold text-xl uppercase text-white mb-6">Network</h4>
                <div class="flex flex-col gap-3">
                    <a href="#" class="group border border-white text-white px-4 py-3 font-tech text-xs uppercase hover:bg-[#ccff00] hover:text-black hover:border-[#ccff00] transition text-center flex justify-between items-center">
                        <span>Instagram</span>
                        <span class="group-hover:rotate-45 transition">-></span>
                    </a>
                    <a href="#" class="group border border-white text-white px-4 py-3 font-tech text-xs uppercase hover:bg-[#ff0099] hover:text-white hover:border-[#ff0099] transition text-center flex justify-between items-center">
                        <span>Telegram</span>
                        <span class="group-hover:rotate-45 transition">-></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="max-w-7xl mx-auto mt-16 pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="font-tech text-[10px] text-gray-600 uppercase">
                © 2025 Artefact Room. All rights reserved. System V.2.0
            </div>
            <div class="font-spray text-gray-500 text-sm rotate-[-2deg]">
                No trends. Just concrete.
            </div>
        </div>
    </footer>

    <script>
        // Simple script to handle size selection in product pages
        document.addEventListener('DOMContentLoaded', function() {
            // Handle size selection
            const sizeRadios = document.querySelectorAll('input[name="size"]');
            sizeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('selectedSize').value = this.value;
                });
            });
            
            // Auto-hide flash messages after 5 seconds
            setTimeout(() => {
                const flashMessages = document.querySelectorAll('[class*="fixed top-20 right-6"]');
                flashMessages.forEach(msg => {
                    msg.style.display = 'none';
                });
            }, 5000);
        });
    </script>
</body>
</html>