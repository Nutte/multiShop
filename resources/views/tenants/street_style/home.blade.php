<!-- FILE: resources/views/tenants/street_style/home.blade.php -->
@extends('layouts.app')

@section('title', 'Street Style - Urban Fashion')
@section('brand_name', 'STREET STYLE 🔥')
@section('nav_class', 'bg-black border-b-4 border-yellow-400')
@section('body_class', 'bg-gray-900 text-white')

@section('content')
    <h1 class="text-4xl font-black text-yellow-400 mb-8 uppercase tracking-widest text-center">New Drop</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($products as $product)
            <div class="bg-gray-800 border-2 border-yellow-400 p-4 transform hover:-translate-y-2 transition duration-300">
                <div class="h-48 bg-gray-700 mb-4 flex items-center justify-center text-4xl">👟</div>
                <h2 class="text-xl font-bold mb-2">{{ $product->name }}</h2>
                <p class="text-yellow-400 text-2xl font-mono">${{ $product->price }}</p>
                <form action="{{ route('cart.add') }}" method="POST" class="mt-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button class="w-full bg-yellow-400 text-black font-bold py-2 hover:bg-yellow-300 uppercase">
                        Cop It
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endsection