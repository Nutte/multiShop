<!-- FILE: resources/views/shop/home.blade.php -->
@extends('layouts.app')

@php
    $currentTenant = app(\App\Services\TenantService::class)->getCurrentTenantId();
    $titles = [
        'street_style' => 'New Drops',
        'designer_hub' => 'Collections',
        'military_gear' => 'Supply Catalog'
    ];
@endphp

@section('content')
    <!-- Hero Section -->
    <div class="py-16 text-center border-b theme-border mb-12 bg-opacity-50">
        <h1 class="text-6xl font-black uppercase tracking-tighter mb-4 theme-skew theme-text">
            {{ $titles[$currentTenant] ?? 'Welcome' }}
        </h1>
        <p class="theme-muted tracking-widest uppercase text-sm">
            {{ date('Y') }} Official Store
        </p>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap justify-center gap-4 mb-12">
        <a href="{{ route('home') }}" class="px-6 py-2 border theme-border font-bold uppercase text-sm transition {{ !request('category') ? 'theme-btn shadow-lg' : 'theme-link' }}">
            All Items
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('home', ['category' => $cat->slug]) }}" class="px-6 py-2 border theme-border font-bold uppercase text-sm transition {{ request('category') == $cat->slug ? 'theme-btn shadow-lg' : 'theme-link' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        @foreach($products as $product)
            <div class="group relative block h-full flex flex-col">
                <a href="{{ route('product.show', $product->slug) }}" class="flex-1 flex flex-col">
                    <div class="relative overflow-hidden theme-card aspect-[3/4] mb-4 group-hover:opacity-90 transition">
                        <img src="{{ $product->cover_url }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-105">
                        
                        @if($product->has_discount)
                            <div class="absolute top-2 right-2 theme-btn px-2 py-1 text-xs shadow-md">
                                SALE
                            </div>
                        @endif
                    </div>
                    
                    <div class="text-center mt-auto">
                        <h3 class="text-lg font-bold uppercase truncate theme-text">{{ $product->name }}</h3>
                        <div class="mt-2 pb-2">
                            <span class="theme-muted text-xs block mb-1">{{ $product->clothingLine->name ?? 'General' }}</span>
                            <span class="block font-bold text-lg" style="color: var(--color-primary)">
                                ${{ $product->current_price }}
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="mt-12">
        {{ $products->links() }}
    </div>
@endsection