<!-- FILE: resources/views/tenants/street_style/home.blade.php -->
@extends('tenants.street_style.layouts.artefact')
@section('title', 'ARTEFACT.ROOM // STREET_MOD')

@section('content')
<main id="view-home" class="w-full pt-20">
    
    <!-- HERO SECTION -->
    <section class="relative w-full min-h-[85vh] flex flex-col items-center justify-center overflow-hidden mb-12">
        <!-- Background Graffiti -->
        <div class="absolute inset-0 opacity-20 pointer-events-none">
            <div class="absolute top-20 left-10 font-spray text-[10rem] text-white leading-none rotate-[-5deg] blur-sm">CHAOS</div>
            <div class="absolute bottom-20 right-10 font-spray text-[8rem] text-white leading-none rotate-[5deg] blur-sm">CONTROL</div>
        </div>

        <div class="max-w-7xl mx-auto w-full px-6 grid grid-cols-1 md:grid-cols-2 gap-12 items-center relative z-10">
            <!-- Text Block -->
            <div class="order-2 md:order-1 relative">
                <h1 class="text-6xl md:text-8xl font-display font-black uppercase leading-[0.8] mb-6 text-white drop-shadow-[5px_5px_0px_#ff0099]">
                    Urban<br>
                    <span class="text-transparent stroke-text" style="-webkit-text-stroke: 2px white;">Armor</span>
                </h1>
                
                <div class="bg-white text-black p-6 max-w-md transform rotate-1 shadow-[10px_10px_0px_rgba(255,255,255,0.2)]">
                    <div class="tape-strip -top-3 left-1/2 -translate-x-1/2 bg-yellow-400/80"></div>
                    <p class="font-tech text-sm leading-relaxed font-bold uppercase">
                        "Мы не следуем трендам. Мы создаем униформу для бетонных джунглей."
                    </p>
                    <div class="mt-4 flex gap-4">
                        <a href="#catalog" class="font-spray text-xl text-pink-600 hover:text-black underline decoration-wavy">Go to Drop -></a>
                    </div>
                </div>
            </div>

            <!-- Hero Image -->
            <div class="order-1 md:order-2 relative group">
                <div class="relative z-10 border-4 border-white bg-white shadow-2xl transform rotate-2 group-hover:rotate-0 transition duration-500">
                    <img src="https://images.unsplash.com/photo-1594631252845-29fc4cc8cde9?q=80&w=1000&auto=format&fit=crop" class="w-full h-auto grayscale contrast-125 hover:grayscale-0 transition duration-500">
                    <div class="absolute -bottom-6 -right-6 bg-black text-white font-tech text-xs px-4 py-2 border border-white">
                        FIG.01 // KYIV
                    </div>
                </div>
                <!-- Spray element -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-pink-500 rounded-full blur-[50px] opacity-40 animate-pulse"></div>
            </div>
        </div>
    </section>

    <!-- CAUTION TAPE DIVIDER -->
    <div class="caution-tape py-3 border-y-4 border-black">
        <div class="caution-scroll">
            WARNING: HIGH VOLTAGE STYLE // DO NOT CROSS // NEW DROP AVAILABLE // KEEP DISTANCE // ARTEFACT.ROOM // WARNING: HIGH VOLTAGE STYLE // DO NOT CROSS // NEW DROP AVAILABLE // KEEP DISTANCE // ARTEFACT.ROOM //
        </div>
    </div>

    <!-- CATALOG SECTION -->
    <section id="catalog" class="relative w-full py-24 bg-[#1a1a1a]">
        
        <div class="max-w-7xl mx-auto px-6">
            <!-- Section Title -->
            <div class="flex items-center justify-between mb-16 relative">
                <h2 class="text-5xl md:text-7xl font-display font-black text-white uppercase italic">
                    The <span class="text-[#ccff00] font-spray not-italic">Drop</span>
                </h2>
                <!-- Graffiti Tag -->
                <div class="absolute -top-10 left-1/4 transform -rotate-12 font-spray text-4xl text-pink-500 opacity-80">
                    Fresh!
                </div>
                <div class="hidden md:block font-tech text-gray-500 text-right border-l-2 border-gray-600 pl-4">
                    SEASON: 2025/Q3<br>
                    STOCK: LIMITED
                </div>
            </div>

            <!-- Product Grid (Poster Wall) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-12">
                
                @foreach($products as $product)
                <div class="poster-card p-2 group cursor-pointer" onclick="window.location.href='{{ route('product.show', $product->slug) }}'">
                    <!-- Top Tape -->
                    <div class="tape-strip -top-3 left-1/2 -translate-x-1/2 w-32 bg-white/50 rotate-[-2deg]"></div>
                    @if($loop->first)
                    <div class="tag-sticker top-2 right-2">New</div>
                    @endif
                    
                    <div class="relative overflow-hidden mb-2 border border-black h-80">
                        <img src="{{ $product->cover_url }}" 
                             class="w-full h-full object-cover art-filter transition duration-500">
                    </div>
                    <div class="p-2 text-center">
                        <h3 class="font-display font-black text-2xl uppercase leading-none mb-1">{{ $product->name }}</h3>
                        <p class="font-tech text-xs text-gray-600 mb-2">{{ $product->clothingLine->name ?? 'COLLECTION' }}</p>
                        <span class="font-spray text-2xl bg-black text-white px-2 py-1 transform -rotate-2 inline-block">₴{{ number_format($product->price * 40, 0) }}</span>
                        
                        <!-- Mini Size Selector on Hover -->
                        <div class="mt-2 flex justify-center gap-1 opacity-0 group-hover:opacity-100 transition duration-300">
                            @if($product->variants->count() > 0)
                                @foreach($product->variants->take(3) as $variant)
                                    <span class="text-[10px] font-tech border border-black px-1 hover:bg-black hover:text-white">{{ $variant->size }}</span>
                                @endforeach
                            @else
                                <span class="text-[10px] font-tech border border-black px-1 hover:bg-black hover:text-white">ONE SIZE</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach

            </div>

            <!-- Load More Button -->
            <div class="mt-16 text-center">
                <a href="{{ route('home') }}?page=2" class="font-display font-black text-white text-xl uppercase border-b-4 border-pink-500 hover:text-pink-500 hover:border-white transition pb-1">
                    View Full Archive
                </a>
            </div>
        </div>
    </section>
</main>
@endsection