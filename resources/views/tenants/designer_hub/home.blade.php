<!-- FILE: resources/views/tenants/designer_hub/home.blade.php -->
@extends('layouts.app')

@section('title', 'Designer Hub - Luxury')
@section('brand_name', 'D E S I G N E R • H U B')
@section('nav_class', 'bg-white text-black border-b border-gray-200 shadow-sm')
@section('body_class', 'bg-white text-gray-800 font-serif')

@section('content')
    <div class="text-center mb-12">
        <h1 class="text-3xl italic font-light mb-2">Autumn Collection</h1>
        <div class="w-16 h-1 bg-black mx-auto"></div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
        @foreach($products as $product)
            <div class="group">
                <div class="h-64 bg-gray-100 mb-6 flex items-center justify-center text-4xl text-gray-400 group-hover:bg-gray-200 transition">👗</div>
                <h2 class="text-lg font-light tracking-wide text-center">{{ $product->name }}</h2>
                <p class="text-center text-gray-500 mt-2">${{ $product->price }}</p>
                <form action="{{ route('cart.add') }}" method="POST" class="mt-4 text-center opacity-0 group-hover:opacity-100 transition">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button class="border border-black px-8 py-2 text-sm hover:bg-black hover:text-white transition">
                        Add to Bag
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endsection