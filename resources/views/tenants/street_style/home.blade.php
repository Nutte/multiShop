<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARTEFACT.ROOM // FULL_STORE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=Syne:wght@400;700;800&family=Michroma&family=Reenie+Beanie&family=Courier+Prime:wght@400;700&family=Permanent+Marker&display=swap" rel="stylesheet">

    <style>
        /* --- CORE VARS --- */
        :root {
            --c-void: #111111;
            --c-concrete: #2a2a2a;
            --c-paper: #f2f0e9;
            --c-neon: #ccff00;
            --c-pink: #ff0099;
            --c-tape: #ffcc00;
        }

        body {
            background-color: var(--c-void);
            color: var(--c-paper);
            font-family: 'Space Mono', monospace;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.08'/%3E%3C/svg%3E");
            overflow-x: hidden;
        }

        /* FONTS */
        .font-syne { font-family: 'Syne', sans-serif; }
        .font-marker { font-family: 'Permanent Marker', cursive; }
        .font-tech { font-family: 'Space Mono', monospace; }
        .font-hand { font-family: 'Reenie Beanie', cursive; }
        .font-receipt { font-family: 'Courier Prime', monospace; }

        /* UI COMPONENTS */
        
        /* 1. Header Blur & Border */
        .header-glass {
            background: rgba(17, 17, 17, 0.85);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* 2. Poster Card (Product) */
        .poster-card {
            background-color: var(--c-paper);
            color: black;
            position: relative;
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
            transition: all 0.3s ease;
        }
        .poster-card:hover {
            transform: translateY(-5px) rotate(1deg);
            box-shadow: 0 15px 30px rgba(0,0,0,0.7), 0 0 0 2px var(--c-neon);
            z-index: 10;
        }

        /* 3. Mobile Menu Overlay */
        .mobile-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--c-void);
            z-index: 100;
            transform: translateX(100%); /* Hidden by default */
            transition: transform 0.4s cubic-bezier(0.77, 0, 0.175, 1);
            display: flex;
            flex-direction: column;
        }
        .mobile-menu.open {
            transform: translateX(0);
        }
        .menu-link {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 3rem;
            line-height: 1;
            color: transparent;
            -webkit-text-stroke: 1px white;
            transition: all 0.3s;
        }
        .menu-link:hover {
            color: var(--c-neon);
            -webkit-text-stroke: 1px var(--c-neon);
            padding-left: 20px;
        }

        /* 4. Filter Tags */
        .filter-btn {
            border: 1px solid #333;
            color: #666;
            transition: 0.2s;
        }
        .filter-btn:hover, .filter-btn.active {
            border-color: var(--c-neon);
            color: var(--c-neon);
            background: rgba(204, 255, 0, 0.05);
        }
        
        /* 5. Custom Checkbox/Radio styling for filters */
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
            background: var(--c-neon);
            border-color: var(--c-neon);
        }
        input:checked + .check-box::after {
            content: 'X';
            color: black;
            font-size: 10px;
            font-weight: bold;
        }

        /* Tape Strip */
        .tape-strip {
            position: absolute;
            background-color: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(2px);
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
            z-index: 20;
        }

        /* Utilities */
        .text-neon { color: var(--c-neon); }
        .bg-neon { background-color: var(--c-neon); }
        .border-neon { border-color: var(--c-neon); }
        .stroke-text { -webkit-text-stroke: 1px white; color: transparent; }
        .hidden { display: none; }
    </style>
</head>
<body class="min-h-screen relative">

    <!-- DEV CONTROL -->
    <div class="fixed top-0 left-0 w-full bg-black z-[200] border-b border-gray-800 p-2 flex flex-wrap gap-2 justify-between items-center text-xs font-mono text-gray-500">
        <div class="flex gap-4">
            <span class="font-bold text-white">DEV_NAV:</span>
            <button onclick="switchView('home')" class="hover:text-white">HOME</button>
            <button onclick="switchView('shop')" class="hover:text-white text-neon">SHOP (ALL)</button>
            <button onclick="switchView('product')" class="hover:text-white">PRODUCT</button>
            <button onclick="switchView('cart')" class="hover:text-white">CART</button>
        </div>
        <div class="hidden md:block">V.3.0 // MOBILE MENU READY</div>
    </div>
    <div class="h-10"></div>

    <!-- ========================================================================= -->
    <!-- HEADER (UPDATED) -->
    <!-- ========================================================================= -->
    <header class="fixed top-10 left-0 w-full z-50 header-glass transition-all duration-300">
        <div class="px-6 h-16 md:h-20 flex items-center justify-between">
            
            <!-- Left: Logo & Burger -->
            <div class="flex items-center gap-6">
                <!-- Mobile Burger -->
                <button onclick="toggleMenu()" class="group flex flex-col gap-1.5 w-8 cursor-pointer z-[110] relative">
                    <span class="w-full h-0.5 bg-white group-hover:bg-neon transition-colors"></span>
                    <span class="w-2/3 h-0.5 bg-white group-hover:w-full group-hover:bg-neon transition-all"></span>
                    <span class="w-full h-0.5 bg-white group-hover:bg-neon transition-colors"></span>
                </button>

                <a href="#" onclick="switchView('home')" class="relative group hidden md:block">
                    <span class="text-2xl font-syne font-black tracking-tighter uppercase italic text-white group-hover:text-neon transition">Artefact</span>
                </a>
            </div>

            <!-- Center: Logo Mobile -->
            <a href="#" onclick="switchView('home')" class="md:hidden">
                <span class="text-xl font-syne font-black uppercase italic text-white">Artefact</span>
            </a>

            <!-- Right: Cart & Search -->
            <div class="flex items-center gap-4 md:gap-8 font-tech text-xs text-white">
                <div class="hidden md:flex items-center gap-2 border-b border-gray-600 pb-1">
                    <span class="text-gray-400">SEARCH_</span>
                    <input type="text" placeholder="type here..." class="bg-transparent outline-none w-24 focus:w-48 transition-all">
                </div>
                
                <a href="#" onclick="switchView('cart')" class="relative group">
                    <span class="uppercase group-hover:text-neon">Cart (2)</span>
                    <div class="absolute -bottom-2 left-0 w-full h-0.5 bg-neon scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                </a>
            </div>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- MOBILE MENU OVERLAY -->
    <!-- ========================================================================= -->
    <div id="mobile-menu" class="mobile-menu p-6 pt-24">
        <!-- Background Text Texture -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-5">
            <div class="font-marker text-[20vh] text-white leading-none whitespace-nowrap rotate-[-10deg] absolute top-20 -left-20">MENU MENU</div>
            <div class="font-marker text-[20vh] text-white leading-none whitespace-nowrap rotate-[-10deg] absolute bottom-20 -right-20">SYSTEM SYSTEM</div>
        </div>

        <!-- Links -->
        <nav class="flex flex-col gap-4 relative z-10">
            <a href="#" onclick="switchView('home'); toggleMenu()" class="menu-link">Home Base</a>
            <a href="#" onclick="switchView('shop'); toggleMenu()" class="menu-link text-neon" style="-webkit-text-stroke: 1px var(--c-neon); color: var(--c-neon);">Shop All</a>
            <a href="#" onclick="switchView('cart'); toggleMenu()" class="menu-link">Cart [2]</a>
            <a href="#" onclick="switchView('auth'); toggleMenu()" class="menu-link">Profile</a>
        </nav>

        <!-- Footer Info -->
        <div class="mt-auto border-t border-gray-800 pt-6 flex justify-between font-tech text-xs text-gray-500">
            <div>
                <p>KYIV, UA</p>
                <p>EST. 2025</p>
            </div>
            <div class="text-right">
                <a href="#" class="block hover:text-white">INSTAGRAM</a>
                <a href="#" class="block hover:text-white">TELEGRAM</a>
            </div>
        </div>
    </div>


    <!-- ========================================================================= -->
    <!-- VIEW: SHOP (CATALOG ARCHIVE) -->
    <!-- ========================================================================= -->
    <main id="view-shop" class="w-full pt-28 pb-20 min-h-screen">
        <div class="max-w-[1400px] mx-auto px-4 md:px-6">
            
            <!-- Page Header -->
            <div class="mb-12 border-b border-gray-800 pb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-neon text-black text-[10px] font-bold px-1 uppercase">Store_ID: #8841</span>
                        <span class="font-tech text-xs text-gray-500">/// FULL_ACCESS</span>
                    </div>
                    <h1 class="text-5xl md:text-7xl font-syne font-black uppercase text-white italic">
                        Warehouse
                    </h1>
                </div>
                <!-- Search Large -->
                <div class="w-full md:w-1/3 relative">
                     <input type="text" placeholder="Search product name..." class="w-full bg-transparent border-b-2 border-gray-700 text-white font-marker text-xl p-2 focus:border-neon outline-none placeholder:text-gray-600 placeholder:font-sans">
                     <svg class="w-6 h-6 text-gray-500 absolute right-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-12">
                
                <!-- SIDEBAR FILTERS -->
                <aside class="w-full lg:w-64 flex-shrink-0 space-y-12">
                    
                    <!-- Categories -->
                    <div>
                        <h3 class="font-syne font-bold text-white uppercase text-lg mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-neon"></span> Category
                        </h3>
                        <div class="space-y-2 font-tech text-sm text-gray-400">
                            <label class="flex items-center cursor-pointer hover:text-white group">
                                <input type="checkbox" class="hidden">
                                <span class="check-box group-hover:border-white"></span>
                                All Items [42]
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-white group">
                                <input type="checkbox" class="hidden">
                                <span class="check-box group-hover:border-white"></span>
                                Hoodies & Sweat
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-white group">
                                <input type="checkbox" class="hidden">
                                <span class="check-box group-hover:border-white"></span>
                                T-Shirts
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-white group">
                                <input type="checkbox" class="hidden">
                                <span class="check-box group-hover:border-white"></span>
                                Pants / Cargo
                            </label>
                            <label class="flex items-center cursor-pointer hover:text-white group">
                                <input type="checkbox" class="hidden">
                                <span class="check-box group-hover:border-white"></span>
                                Accessories
                            </label>
                        </div>
                    </div>

                    <!-- Size Filter -->
                    <div>
                        <h3 class="font-syne font-bold text-white uppercase text-lg mb-4">Size_Spec</h3>
                        <div class="grid grid-cols-4 gap-2">
                            <button class="filter-btn h-10 font-tech text-xs font-bold uppercase hover:bg-white hover:text-black hover:border-white">XS</button>
                            <button class="filter-btn h-10 font-tech text-xs font-bold uppercase active">S</button>
                            <button class="filter-btn h-10 font-tech text-xs font-bold uppercase">M</button>
                            <button class="filter-btn h-10 font-tech text-xs font-bold uppercase">L</button>
                            <button class="filter-btn h-10 font-tech text-xs font-bold uppercase">XL</button>
                            <button class="filter-btn h-10 font-tech text-xs font-bold uppercase">XXL</button>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <h3 class="font-syne font-bold text-white uppercase text-lg mb-4">Price_Limit</h3>
                        <div class="flex items-center justify-between text-gray-400 font-tech text-xs mb-2">
                            <span>0</span>
                            <span>10,000 ₴</span>
                        </div>
                        <input type="range" class="w-full accent-[#ccff00] bg-gray-700 h-1 appearance-none rounded-lg cursor-pointer">
                        <div class="mt-4 flex justify-between">
                             <input type="number" placeholder="Min" class="w-20 bg-transparent border border-gray-700 text-white font-tech text-xs p-1 text-center">
                             <input type="number" placeholder="Max" class="w-20 bg-transparent border border-gray-700 text-white font-tech text-xs p-1 text-center">
                        </div>
                    </div>

                    <!-- Collections -->
                    <div>
                        <h3 class="font-syne font-bold text-white uppercase text-lg mb-4">Collection</h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="border border-pink-500 text-pink-500 px-2 py-1 font-marker text-xs transform -rotate-2 cursor-pointer hover:bg-pink-500 hover:text-white transition">Acid Drop</span>
                            <span class="border border-gray-500 text-gray-500 px-2 py-1 font-tech text-xs cursor-pointer hover:border-white hover:text-white transition">Core Basics</span>
                            <span class="border border-gray-500 text-gray-500 px-2 py-1 font-tech text-xs cursor-pointer hover:border-white hover:text-white transition">Night Ops</span>
                        </div>
                    </div>
                </aside>

                <!-- PRODUCTS GRID -->
                <div class="flex-1">
                    <!-- Sorting Bar -->
                    <div class="flex justify-between items-center mb-6 font-tech text-xs text-gray-500 border-b border-gray-800 pb-4">
                        <span>SHOWING 1-9 OF 42</span>
                        <div class="flex items-center gap-2">
                            <span>SORT BY:</span>
                            <select class="bg-transparent text-white border-none outline-none cursor-pointer uppercase font-bold">
                                <option class="bg-black">Newest First</option>
                                <option class="bg-black">Price: Low to High</option>
                                <option class="bg-black">Price: High to Low</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 gap-y-12">
                        
                        <!-- Product 1 -->
                        <div class="poster-card p-2 group cursor-pointer" onclick="switchView('product')">
                            <div class="tape-strip -top-2 left-1/2 -translate-x-1/2 w-20 h-6 bg-white/40 rotate-1"></div>
                            <div class="relative overflow-hidden mb-2 border border-black h-64 bg-gray-200">
                                <img src="https://images.unsplash.com/photo-1523398002811-999ca8dec234?q=80&w=600" class="w-full h-full object-cover filter grayscale contrast-125 group-hover:grayscale-0 transition duration-500">
                                <div class="absolute bottom-2 left-2 bg-neon text-black text-[10px] font-bold px-1">NEW ARRIVAL</div>
                            </div>
                            <div class="p-2">
                                <h3 class="font-syne font-bold text-xl uppercase leading-none">Sketch Tee</h3>
                                <div class="flex justify-between items-end mt-2">
                                    <span class="font-tech text-xs text-gray-500">COTTON / BLK</span>
                                    <span class="font-marker text-lg">₴1,200</span>
                                </div>
                            </div>
                        </div>

                        <!-- Product 2 -->
                        <div class="poster-card p-2 group cursor-pointer" onclick="switchView('product')">
                            <div class="tape-strip -top-2 right-4 w-16 h-6 bg-white/40 -rotate-2"></div>
                            <div class="relative overflow-hidden mb-2 border border-black h-64 bg-gray-200">
                                <img src="https://images.unsplash.com/photo-1578932750294-f5075e85f44a?q=80&w=600" class="w-full h-full object-cover filter grayscale contrast-125 group-hover:grayscale-0 transition duration-500">
                            </div>
                            <div class="p-2">
                                <h3 class="font-syne font-bold text-xl uppercase leading-none">Utility Hoodie</h3>
                                <div class="flex justify-between items-end mt-2">
                                    <span class="font-tech text-xs text-gray-500">HEAVY / GRY</span>
                                    <span class="font-marker text-lg">₴2,400</span>
                                </div>
                            </div>
                        </div>

                        <!-- Product 3 -->
                        <div class="poster-card p-2 group cursor-pointer" onclick="switchView('product')">
                            <div class="tape-strip -top-2 left-4 w-16 h-6 bg-white/40 rotate-3"></div>
                            <div class="absolute top-2 right-2 z-10 font-marker text-pink-500 text-sm rotate-12 bg-white px-1 border border-black shadow-sm">HOT</div>
                            <div class="relative overflow-hidden mb-2 border border-black h-64 bg-gray-200">
                                <img src="https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?q=80&w=600" class="w-full h-full object-cover filter grayscale contrast-125 group-hover:grayscale-0 transition duration-500">
                            </div>
                            <div class="p-2">
                                <h3 class="font-syne font-bold text-xl uppercase leading-none">Cargo Pants</h3>
                                <div class="flex justify-between items-end mt-2">
                                    <span class="font-tech text-xs text-gray-500">RIPSTOP / KHK</span>
                                    <span class="font-marker text-lg">₴2,800</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Product 4 -->
                        <div class="poster-card p-2 group cursor-pointer opacity-70" onclick="switchView('product')">
                            <div class="tape-strip -top-2 left-1/2 -translate-x-1/2 w-20 h-6 bg-white/40 -rotate-1"></div>
                            <div class="relative overflow-hidden mb-2 border border-black h-64 bg-gray-200 flex items-center justify-center">
                                <span class="font-marker text-2xl rotate-[-15deg] text-gray-400">SOLD OUT</span>
                            </div>
                            <div class="p-2">
                                <h3 class="font-syne font-bold text-xl uppercase leading-none text-gray-500">Tac Vest</h3>
                                <div class="flex justify-between items-end mt-2">
                                    <span class="font-tech text-xs text-gray-500">NYLON / BLK</span>
                                    <span class="font-marker text-lg line-through text-red-500">₴3,200</span>
                                </div>
                            </div>
                        </div>
                         
                         <!-- Product 5 -->
                        <div class="poster-card p-2 group cursor-pointer" onclick="switchView('product')">
                            <div class="tape-strip -top-2 right-2 w-12 h-6 bg-white/40 rotate-2"></div>
                            <div class="relative overflow-hidden mb-2 border border-black h-64 bg-gray-200">
                                <img src="https://images.unsplash.com/photo-1523398002811-999ca8dec234?q=80&w=600" class="w-full h-full object-cover filter grayscale contrast-125 group-hover:grayscale-0 transition duration-500">
                            </div>
                            <div class="p-2">
                                <h3 class="font-syne font-bold text-xl uppercase leading-none">Basic Tee</h3>
                                <div class="flex justify-between items-end mt-2">
                                    <span class="font-tech text-xs text-gray-500">COTTON / WHT</span>
                                    <span class="font-marker text-lg">₴800</span>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-16 border-t border-gray-800 pt-8 flex justify-between items-center font-tech text-xs text-gray-500">
                         <button class="hover:text-white hover:underline"><- PREV</button>
                         <div class="flex gap-4">
                             <span class="text-neon border-b border-neon">1</span>
                             <span class="hover:text-white cursor-pointer">2</span>
                             <span class="hover:text-white cursor-pointer">3</span>
                             <span>...</span>
                             <span class="hover:text-white cursor-pointer">8</span>
                         </div>
                         <button class="hover:text-white hover:underline">NEXT -></button>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- VIEW: HOME (EXISTING) -->
    <!-- ========================================================================= -->
    <main id="view-home" class="w-full hidden pt-20">
        <!-- HERO SECTION -->
        <section class="relative w-full min-h-[85vh] flex flex-col items-center justify-center overflow-hidden mb-12">
            <div class="absolute inset-0 opacity-20 pointer-events-none">
                <div class="absolute top-20 left-10 font-marker text-[10rem] text-white leading-none rotate-[-5deg] blur-sm">CHAOS</div>
                <div class="absolute bottom-20 right-10 font-marker text-[8rem] text-white leading-none rotate-[5deg] blur-sm">CONTROL</div>
            </div>
            <div class="max-w-7xl mx-auto w-full px-6 grid grid-cols-1 md:grid-cols-2 gap-12 items-center relative z-10">
                <div class="order-2 md:order-1 relative">
                    <h1 class="text-6xl md:text-8xl font-syne font-black uppercase leading-[0.8] mb-6 text-white drop-shadow-[5px_5px_0px_#ff0099]">
                        Urban<br><span class="stroke-text" style="-webkit-text-stroke: 2px white;">Armor</span>
                    </h1>
                    <div class="bg-white text-black p-6 max-w-md transform rotate-1 shadow-[10px_10px_0px_rgba(255,255,255,0.2)]">
                        <div class="tape-strip -top-3 left-1/2 -translate-x-1/2 bg-yellow-400/80 w-32 h-8"></div>
                        <p class="font-tech text-sm leading-relaxed font-bold uppercase">"Мы не следуем трендам. Мы создаем униформу для бетонных джунглей."</p>
                        <div class="mt-4 flex gap-4"><a href="#" onclick="switchView('shop')" class="font-marker text-xl text-pink-600 hover:text-black underline decoration-wavy">Go to Drop -></a></div>
                    </div>
                </div>
                <div class="order-1 md:order-2 relative group">
                    <div class="relative z-10 border-4 border-white bg-white shadow-2xl transform rotate-2 group-hover:rotate-0 transition duration-500">
                        <img src="https://images.unsplash.com/photo-1594631252845-29fc4cc8cde9?q=80&w=1000&auto=format&fit=crop" class="w-full h-auto grayscale contrast-125 hover:grayscale-0 transition duration-500">
                    </div>
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-pink-500 rounded-full blur-[50px] opacity-40 animate-pulse"></div>
                </div>
            </div>
        </section>
        
        <!-- Quick Links -->
        <div class="bg-neon py-3 border-y-4 border-black font-michroma text-black font-black uppercase tracking-widest overflow-hidden whitespace-nowrap">
            WARNING: HIGH VOLTAGE STYLE // DO NOT CROSS // NEW DROP AVAILABLE // KEEP DISTANCE // ARTEFACT.ROOM //
        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- VIEW: PRODUCT (EXISTING) -->
    <!-- ========================================================================= -->
    <main id="view-product" class="w-full hidden pt-24 pb-20">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-16">
            <div class="relative">
                <div class="poster-card p-4 rotate-1 bg-paper">
                    <img src="https://images.unsplash.com/photo-1578932750294-f5075e85f44a?q=80&w=1200" class="w-full h-auto filter grayscale contrast-110">
                </div>
            </div>
            <div class="text-white relative">
                <h1 class="font-syne font-black text-6xl uppercase mb-2">Utility<br>Hoodie</h1>
                <div class="font-hand text-3xl text-blue-500 mb-8 rotate-[-1deg]">"Night city fit"</div>
                <div class="bg-[#1a1a1a] border-l-4 border-blue-600 p-6 mb-8 text-gray-300 font-tech text-sm leading-relaxed">Heavy cotton. Hand printed. Unique piece.</div>
                <div class="mb-8 border-t border-gray-800 pt-6">
                    <label class="font-marker text-xl text-neon mb-4 block transform -rotate-1">Select Size_</label>
                    <div class="flex gap-4">
                        <button class="w-12 h-12 border-2 border-white text-white font-tech hover:bg-neon hover:text-black hover:border-neon transition">S</button>
                        <button class="w-12 h-12 border-2 border-white bg-neon text-black border-neon font-tech">M</button>
                        <button class="w-12 h-12 border-2 border-white text-white font-tech hover:bg-neon hover:text-black hover:border-neon transition">L</button>
                    </div>
                </div>
                <button onclick="switchView('cart')" class="bg-white text-black font-syne font-black text-xl uppercase px-12 py-4 hover:bg-pink-500 hover:text-white transition transform hover:scale-105 shadow-[6px_6px_0px_#ccff00]">Add to Bag</button>
            </div>
        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- VIEW: CART (EXISTING) -->
    <!-- ========================================================================= -->
    <main id="view-cart" class="w-full hidden pt-24 pb-20">
        <div class="max-w-4xl mx-auto px-6">
            <h2 class="text-4xl font-syne font-bold text-white uppercase mb-8">Cart Manifest</h2>
            <div class="poster-card p-8 relative rotate-[0.5deg] bg-paper">
                <div class="grid grid-cols-12 gap-4 border-b-2 border-black/80 pb-2 mb-6 font-tech text-xs font-bold uppercase text-gray-500">
                    <div class="col-span-8">Item</div>
                    <div class="col-span-4 text-right">Total</div>
                </div>
                <div class="grid grid-cols-12 gap-4 items-center mb-4">
                    <div class="col-span-8 flex gap-4">
                        <div class="w-16 h-20 bg-gray-200 border border-black"><img src="https://images.unsplash.com/photo-1578932750294-f5075e85f44a?q=80&w=200" class="w-full h-full object-cover grayscale"></div>
                        <div>
                            <h3 class="font-bold text-lg font-syne uppercase">Utility Hoodie</h3>
                            <p class="font-tech text-[10px] text-gray-500">M // BLACK</p>
                        </div>
                    </div>
                    <div class="col-span-4 text-right font-tech font-bold text-lg">₴2,400</div>
                </div>
                <button class="mt-8 bg-black text-white font-tech text-sm uppercase px-8 py-4 w-full hover:bg-neon hover:text-black transition">Checkout -></button>
            </div>
        </div>
    </main>

    <!-- ========================================================================= -->
    <!-- VIEW: AUTH (EXISTING) -->
    <!-- ========================================================================= -->
    <main id="view-auth" class="w-full hidden min-h-[80vh] flex items-center justify-center pt-20">
        <div class="poster-card p-10 max-w-md w-full mx-6 rotate-[-1deg] bg-paper">
            <h2 class="font-syne font-bold text-3xl uppercase text-center mb-8">Access</h2>
            <input type="email" placeholder="EMAIL" class="w-full bg-transparent border-b-2 border-gray-300 font-hand text-xl p-2 mb-4 outline-none">
            <input type="password" placeholder="KEY" class="w-full bg-transparent border-b-2 border-gray-300 font-hand text-xl p-2 mb-8 outline-none">
            <button onclick="switchView('home')" class="w-full border-2 border-black py-3 font-bold font-tech text-sm uppercase hover:bg-black hover:text-white transition">Login</button>
        </div>
    </main>

    <!-- FOOTER (SHARED) -->
    <footer class="border-t-4 border-white bg-black pt-16 pb-8 px-6 relative overflow-hidden">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 text-white relative z-10">
            <div>
                <h3 class="font-syne font-black text-2xl italic">Artefact<span class="text-neon not-italic">.Room</span></h3>
                <p class="font-tech text-xs text-gray-400 mt-2">Streetwear Archive.<br>Kyiv, UA.</p>
            </div>
            <div>
                 <h4 class="font-syne font-bold uppercase mb-4">Explore</h4>
                 <ul class="font-tech text-xs text-gray-400 space-y-2">
                     <li><a href="#" onclick="switchView('shop')" class="hover:text-neon">All Products</a></li>
                     <li><a href="#" onclick="switchView('home')" class="hover:text-neon">Lookbook</a></li>
                 </ul>
            </div>
        </div>
    </footer>

    <!-- JS LOGIC -->
    <script>
        function toggleMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('open');
        }

        function switchView(viewName) {
            // Hide all views
            ['home', 'shop', 'product', 'cart', 'auth'].forEach(id => {
                const el = document.getElementById('view-' + id);
                if (el) el.classList.add('hidden');
            });

            // Show selected view
            const view = document.getElementById('view-' + viewName);
            if(view) {
                view.classList.remove('hidden');
                window.scrollTo(0,0);
            }
        }

        // Init
        // Start on Shop or Home based on preference, let's start on Home for effect
        // switchView('home'); 
        // Or actually show the new Shop view as requested:
        switchView('shop');
    </script>
</body>
</html>