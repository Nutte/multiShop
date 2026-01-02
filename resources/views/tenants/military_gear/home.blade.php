@extends('tenants.military_gear.layouts.military')
@section('title', 'KARAKURT | Tactical Art Wear')

@section('content')
<!-- Hero Section -->
<section class="relative h-[85vh] w-full flex items-center justify-center overflow-hidden border-b border-military-gray">
    <!-- Background Image Placeholder (Darkened) -->
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1542353436-312f0e1f67ff?q=80&w=2000&auto=format&fit=crop" alt="Hero Background" class="w-full h-full object-cover opacity-30 grayscale contrast-125">
        <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-[#050505]/60 to-transparent"></div>
        <div class="absolute inset-0 bg-grid-pattern bg-grid opacity-10"></div>
    </div>

    <!-- Hero Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 w-full flex flex-col items-start">
        <div class="flex items-center space-x-4 mb-4">
            <span class="px-2 py-0.5 bg-military-accent text-black font-mono text-xs font-bold uppercase">New Season</span>
            <span class="font-mono text-military-text text-xs tracking-widest">REF: 2024-Q3 // TACTICAL ART</span>
        </div>
        
        <h1 class="text-5xl md:text-8xl font-bold uppercase tracking-tighter text-white mb-6 leading-[0.9]">
            Urban <br/>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-military-gray">Warfare</span> <br/>
            Aesthetics
        </h1>
        
        <p class="max-w-xl text-military-text text-lg mb-10 border-l-2 border-military-accent pl-6">
            Одежда как броня для городской среды. Авторские принты, милитари-крой и бескомпромиссное качество материалов.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="#new-arrivals" class="group relative px-8 py-4 bg-military-light text-black font-bold uppercase tracking-widest overflow-hidden">
                <span class="relative z-10 group-hover:text-white transition-colors">Смотреть каталог</span>
                <div class="absolute inset-0 bg-military-accent transform -translate-x-full skew-x-12 group-hover:translate-x-0 transition-transform duration-300 ease-out"></div>
            </a>
            <a href="#" class="px-8 py-4 border border-military-gray text-white font-mono uppercase tracking-widest hover:bg-military-gray/50 transition-colors flex items-center gap-2">
                <span>О бренде</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>
    </div>

    <!-- Decorative UI Elements -->
    <div class="absolute bottom-10 right-10 hidden md:block text-right font-mono text-xs text-military-text opacity-50">
        <p>LAT: 50.4501 N</p>
        <p>LNG: 30.5234 E</p>
        <p>STATUS: ONLINE</p>
    </div>
</section>

<!-- Marquee / Ticker -->
<div class="w-full bg-military-accent text-black overflow-hidden py-2 border-y border-black">
    <div class="whitespace-nowrap flex gap-10 font-mono font-bold uppercase text-sm">
        <span>/// Limited Edition Prints ///</span>
        <span>Designed in Ukraine</span>
        <span>/// Heavyweight Cotton ///</span>
        <span>Worldwide Shipping</span>
        <span>/// Limited Edition Prints ///</span>
        <span>Designed in Ukraine</span>
        <span>/// Heavyweight Cotton ///</span>
        <span>Worldwide Shipping</span>
    </div>
</div>

<!-- New Arrivals Section -->
<section id="new-arrivals" class="max-w-7xl mx-auto px-4 py-24 relative">
    <div class="flex justify-between items-end mb-12 border-b border-military-gray pb-4">
        <h2 class="text-3xl md:text-5xl font-bold uppercase text-white">Новые поступления</h2>
        <a href="{{ route('home') }}" class="hidden md:block font-mono text-military-accent text-sm hover:underline hover:text-white transition-colors">Смотреть все [Show_All]</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($products as $product)
        <div class="group relative tech-border bg-military-dark p-2 transition-transform duration-300 hover:-translate-y-1 cursor-pointer">
        <a href="{{ route('product.show', $product->slug) }}">
            <div class="corner-accent opacity-0 group-hover:opacity-100 transition-opacity"></div>
            
            <!-- Image -->
            <div class="relative aspect-[3/4] overflow-hidden bg-military-black mb-4">
                <img src="{{ $product->cover_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 opacity-90 group-hover:opacity-100">
                
                @if($product->is_new)
                <!-- Overlay Badge -->
                <div class="absolute top-2 left-2 bg-black/80 backdrop-blur text-white text-[10px] font-mono px-2 py-1 border border-military-gray">
                    NEW DROP
                </div>
                @endif
                
                <!-- Quick Add (Visible on Hover) -->
                <form action="{{ route('cart.add') }}" method="POST" class="absolute bottom-0 left-0 w-full translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="size" value="M">
                    <button type="submit" class="w-full bg-military-accent text-black font-bold uppercase py-3 font-sans text-sm flex justify-center items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        В корзину
                    </button>
                </form>
            </div>

            <!-- Info -->
            <div class="flex justify-between items-start">
                <div>
                    
                    <h3 class="text-white font-bold uppercase tracking-wide group-hover:text-military-accent transition-colors">{{ $product->name }}</h3>
                    
                    <p class="text-military-text text-xs font-mono mt-1">{{ $product->clothingLine->name ?? 'Collection' }}</p>
                </div>
                <div class="text-right">
                    @if($product->has_discount)
                        <span class="text-military-accent font-mono font-bold">{{ number_format($product->sale_price) }} ₴</span>
                        <span class="text-military-text text-xs line-through decoration-military-accent block">{{ number_format($product->price) }} ₴</span>
                    @else
                        <span class="text-white font-mono font-bold">{{ number_format($product->price) }} ₴</span>
                    @endif
                </div>
            </div>
        </a>
        </div>
        @endforeach
    </div>
    
    <div class="mt-12">
        {{ $products->links() }}
    </div>
</section>
@endsection