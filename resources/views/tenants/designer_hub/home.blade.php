@extends('tenants.designer_hub.layouts.gadyuka')

@php
    $currentTenant = app(\App\Services\TenantService::class)->getCurrentTenantId();
    $titles = [
        'street_style' => 'New Drops',
        'designer_hub' => 'Collections',
        'military_gear' => 'Supply Catalog'
    ];
@endphp

@section('title', $titles[$currentTenant] ?? 'GADYUKA.BRAND')

@section('content')
    <!-- Hero Section -->
    <section class="relative px-4 sm:px-6 mb-24 max-w-[1400px] mx-auto">
        <div class="relative h-[80vh] w-full border-2 border-white overflow-hidden group">
            <!-- Manga Speed Lines Overlay -->
            <div class="absolute inset-0 bg-repeating-conic-gradient from-transparent via-[rgba(255,255,255,0.03)] z-10 opacity-30" style="background: repeating-conic-gradient(from 0deg, transparent 0deg 10deg, rgba(255,255,255,0.03) 10deg 12deg); pointer-events: none;"></div>

            <img src="https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=2079&auto=format&fit=crop" class="w-full h-full object-cover img-manga" alt="Hero">

            <!-- Overlay Text -->
            <div class="absolute inset-0 flex flex-col justify-center items-center z-20 pointer-events-none">
                <div class="relative">
                    <h1 class="text-7xl md:text-9xl font-display font-black stroke-white uppercase italic tracking-tighter relative z-10">
                        {{ $titles[$currentTenant] ?? 'GADYUKA' }}
                    </h1>
                    <!-- Glitch Shadow -->
                    <h1 class="text-7xl md:text-9xl font-display font-black text-brand-accent absolute top-1 left-1 opacity-50 z-0 uppercase italic tracking-tighter" style="clip-path: inset(40% 0 40% 0);">
                        {{ $titles[$currentTenant] ?? 'GADYUKA' }}
                    </h1>
                </div>
                <p class="mt-4 bg-brand-accent text-black px-2 py-1 font-mono font-bold text-lg uppercase transform -rotate-2">
                    Available Now / 今すぐ利用可能
                </p>
            </div>
        </div>
    </section>

    <!-- Filters -->
    <div class="flex flex-wrap justify-center gap-4 mb-12">
        <a href="{{ route('home') }}" class="px-6 py-2 border border-gray-600 font-mono uppercase text-xs transition {{ !request('category') ? 'bg-white text-black border-white font-bold' : 'text-gray-400 hover:text-white hover:border-white' }}">
            [ALL_ITEMS]
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('home', ['category' => $cat->slug]) }}" class="px-6 py-2 border border-gray-600 font-mono uppercase text-xs transition {{ request('category') == $cat->slug ? 'bg-white text-black border-white font-bold' : 'text-gray-400 hover:text-white hover:border-white' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        @foreach($products as $product)
            <div class="group cursor-pointer relative" onclick="window.location='{{ route('product.show', $product->slug) }}'">
                <div class="border-2 border-white bg-black relative z-10 overflow-hidden">
                    @if($product->has_discount)
                        <div class="absolute top-0 right-0 bg-brand-accent text-white font-bold font-mono text-xs px-2 py-1 z-20 border border-white">
                            -{{ $product->discount_percentage }}%
                        </div>
                    @elseif($loop->first)
                        <div class="absolute top-0 right-0 bg-white text-black font-bold font-mono text-xs px-2 py-1 z-20">NEW</div>
                    @endif
                    
                    <div class="aspect-[4/5] overflow-hidden">
                        <img src="{{ $product->cover_url }}" class="w-full h-full object-cover img-manga">
                    </div>

                    <div class="p-4 border-t-2 border-white bg-black">
                        <h3 class="font-display font-bold text-xl uppercase glitch-hover truncate">{{ $product->name }}</h3>
                        <div class="flex justify-between items-end mt-2">
                            <p class="font-mono text-gray-400 text-xs">{{ $product->clothingLine->name ?? 'General' }}</p>
                            <div class="text-right">
                                <span class="font-mono font-bold text-lg text-brand-accent">
                                    ${{ $product->current_price }}
                                </span>
                                @if($product->has_discount)
                                    <span class="text-xs line-through text-gray-500 block">${{ $product->price }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Drop Shadow Effect -->
                <div class="absolute inset-0 bg-brand-accent translate-x-2 translate-y-2 z-0 border-2 border-brand-accent"></div>
            </div>
        @endforeach
    </div>

    <div class="mt-12">
        {{ $products->links() }}
    </div>
@endsection