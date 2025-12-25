<!-- FILE: resources/views/tenants/street_style/home.blade.php -->
@extends('layouts.app')

@section('title', 'Urban Collection')
@section('body_class', 'bg-black text-white font-sans')
@section('nav_class', 'bg-black border-b border-yellow-400')
@section('brand_name', 'STREET STYLE')

@section('content')
    <!-- Hero Banner -->
    <div class="relative bg-gray-900 h-64 mb-12 flex items-center justify-center overflow-hidden border-b-2 border-yellow-400">
        <div class="text-center z-10">
            <h1 class="text-6xl font-black italic tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-pink-500">
                NEW DROP 2025
            </h1>
            <p class="text-gray-300 mt-2 tracking-widest uppercase text-sm">Limited Edition Streetwear</p>
        </div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-30"></div>
    </div>

    <!-- Categories Filters -->
    <div class="flex flex-wrap justify-center gap-4 mb-12">
        <a href="{{ route('home') }}" class="px-6 py-2 border-2 {{ !request('category') ? 'border-yellow-400 text-yellow-400' : 'border-gray-700 text-gray-500 hover:border-white hover:text-white' }} font-bold uppercase text-sm transition skew-x-[-10deg]">
            All
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('home', ['category' => $cat->slug]) }}" class="px-6 py-2 border-2 {{ request('category') == $cat->slug ? 'border-yellow-400 text-yellow-400' : 'border-gray-700 text-gray-500 hover:border-white hover:text-white' }} font-bold uppercase text-sm transition skew-x-[-10deg]">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        @foreach($products as $product)
            <div class="group relative block">
                <a href="{{ route('product.show', $product->slug) }}">
                    <div class="relative overflow-hidden border-2 border-gray-800 group-hover:border-yellow-400 transition-colors duration-300">
                        <img src="{{ $product->cover_url }}" class="w-full h-80 object-cover grayscale group-hover:grayscale-0 transition duration-500">
                        
                        @if($product->has_discount)
                            <div class="absolute top-0 right-0 bg-red-600 text-white font-bold px-2 py-1 text-xs transform translate-x-2 -translate-y-2 group-hover:translate-x-0 group-hover:translate-y-0 transition">
                                SALE
                            </div>
                        @endif

                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition flex items-center justify-center">
                            <span class="bg-yellow-400 text-black font-black px-4 py-2 uppercase transform scale-0 group-hover:scale-100 transition duration-300 skew-x-[-10deg]">
                                View Item
                            </span>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h3 class="text-xl font-bold italic uppercase truncate">{{ $product->name }}</h3>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-gray-400 text-sm">{{ $product->clothingLine->name ?? 'Basic' }}</span>
                            <span class="text-yellow-400 font-mono text-lg">
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