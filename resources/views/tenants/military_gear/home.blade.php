<!-- FILE: resources/views/tenants/military_gear/home.blade.php -->
@extends('layouts.app')

@section('title', 'Tactical Gear')
@section('body_class', 'bg-stone-900 text-stone-100 font-mono')
@section('nav_class', 'bg-stone-800 border-b-4 border-orange-700')
@section('brand_name', 'MILITARY GEAR [TAC-OPS]')

@section('content')
    <!-- Hero -->
    <div class="bg-stone-800 p-8 mb-8 border-l-4 border-orange-700">
        <h1 class="text-3xl font-bold uppercase mb-2">Mission Ready Equipment</h1>
        <p class="text-stone-400 text-sm">Professional grade tactical gear for extreme conditions.</p>
    </div>

    <!-- Products -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Sidebar Filters -->
        <div class="col-span-1">
            <div class="bg-stone-800 p-4 rounded border border-stone-700">
                <h3 class="font-bold text-orange-600 uppercase mb-4 text-sm">Categories</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-orange-500 {{ !request('category') ? 'text-white font-bold' : 'text-stone-400' }}">>> ALL GEAR</a></li>
                    @foreach($categories as $cat)
                        <li><a href="{{ route('home', ['category' => $cat->slug]) }}" class="hover:text-orange-500 {{ request('category') == $cat->slug ? 'text-white font-bold' : 'text-stone-400' }}">>> {{ strtoupper($cat->name) }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Grid -->
        <div class="col-span-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($products as $product)
                <div class="bg-stone-800 border border-stone-700 hover:border-orange-700 transition relative group">
                    <a href="{{ route('product.show', $product->slug) }}">
                        <div class="aspect-square overflow-hidden bg-stone-900">
                            <img src="{{ $product->cover_url }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition">
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-sm uppercase truncate mb-1">{{ $product->name }}</h3>
                            <div class="flex justify-between items-center">
                                <span class="text-orange-500 font-bold">${{ $product->current_price }}</span>
                                @if($product->stock_quantity > 0)
                                    <span class="text-[10px] bg-stone-700 px-1 text-green-400">IN STOCK</span>
                                @else
                                    <span class="text-[10px] bg-red-900 px-1 text-red-200">SOLD OUT</span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-8">
        {{ $products->links() }}
    </div>
@endsection