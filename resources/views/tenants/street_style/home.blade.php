<!-- FILE: resources/views/tenants/street_style/home.blade.php -->
@extends('layouts.app')

@section('title', 'Street Style - Urban Fashion')
@section('brand_name', 'STREET STYLE 🔥')
@section('nav_class', 'bg-black border-b-4 border-yellow-400')
@section('body_class', 'bg-gray-900 text-white')

@section('content')
    <!-- Categories Filter -->
    <div class="flex gap-4 overflow-x-auto pb-4 mb-8 border-b border-gray-800">
        <a href="/" class="text-yellow-400 font-bold uppercase whitespace-nowrap hover:text-white">All</a>
        @foreach($categories as $cat)
            <a href="/?category={{ $cat->slug }}" class="text-gray-400 font-bold uppercase whitespace-nowrap hover:text-yellow-400">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <h1 class="text-4xl font-black text-yellow-400 mb-8 uppercase tracking-widest text-center">New Drop</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($products as $product)
            <div class="bg-gray-800 border-2 border-yellow-400 p-4 transform hover:-translate-y-2 transition duration-300 flex flex-col">
                <a href="{{ route('product.show', $product->slug) }}" class="block flex-1">
                    <div class="h-64 bg-gray-700 mb-4 overflow-hidden flex items-center justify-center relative group">
                        <!-- Используем cover_url -->
                        <img src="{{ $product->cover_url }}" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        
                        <!-- Если картинок больше одной, можно показать вторую при ховере (опционально) -->
                        @if($product->images->count() > 1)
                             <img src="{{ $product->images[1]->url }}" class="absolute inset-0 w-full h-full object-cover opacity-0 group-hover:opacity-100 transition duration-500">
                        @endif
                    </div>
                    <h2 class="text-xl font-bold mb-2">{{ $product->name }}</h2>
                    <p class="text-yellow-400 text-2xl font-mono">${{ $product->price }}</p>
                </a>
                <form action="{{ route('cart.add') }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button class="w-full bg-yellow-400 text-black font-bold py-2 hover:bg-yellow-300 uppercase">
                        Cop It
                    </button>
                </form>
            </div>
        @empty
            <div class="col-span-3 text-center text-gray-500 py-12">No products found.</div>
        @endforelse
    </div>
    
    <div class="mt-8">
        {{ $products->links() }}
    </div>
@endsection 