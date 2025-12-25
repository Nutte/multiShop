<!-- FILE: resources/views/tenants/designer_hub/home.blade.php -->
@extends('layouts.app')

@section('title', 'Luxury Collections')
@section('body_class', 'bg-white text-gray-900 font-serif')
@section('nav_class', 'bg-white border-b border-gray-200 text-gray-900')
@section('brand_name', 'DESIGNER HUB')

@section('content')
    <!-- Hero Section -->
    <div class="text-center py-12 mb-12 border-b">
        <h1 class="text-5xl font-thin tracking-widest uppercase mb-4">Elegance Redefined</h1>
        <p class="text-gray-500 italic">Exclusive collections from Milan & Paris</p>
    </div>

    <!-- Filters (Minimalist) -->
    <div class="flex justify-center gap-8 mb-12 text-sm uppercase tracking-widest text-gray-500">
        <a href="{{ route('home') }}" class="{{ !request('category') ? 'text-black font-bold border-b border-black' : 'hover:text-black' }}">All</a>
        @foreach($categories as $cat)
            <a href="{{ route('home', ['category' => $cat->slug]) }}" class="{{ request('category') == $cat->slug ? 'text-black font-bold border-b border-black' : 'hover:text-black' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
        @foreach($products as $product)
            <div class="group">
                <a href="{{ route('product.show', $product->slug) }}">
                    <div class="relative overflow-hidden mb-4 aspect-[3/4]">
                        <img src="{{ $product->cover_url }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-105">
                        @if($product->has_discount)
                            <span class="absolute top-2 right-2 bg-black text-white text-[10px] px-2 py-1 uppercase tracking-widest">Sale</span>
                        @endif
                    </div>
                    <div class="text-center">
                        <h3 class="text-lg font-normal mb-1">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-500">
                            @if($product->has_discount)
                                <span class="line-through mr-2">${{ $product->price }}</span>
                                <span class="text-red-800">${{ $product->sale_price }}</span>
                            @else
                                ${{ $product->price }}
                            @endif
                        </p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="mt-12">
        {{ $products->links() }}
    </div>
@endsection