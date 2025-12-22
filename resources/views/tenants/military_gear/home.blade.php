<!-- FILE: resources/views/tenants/military_gear/home.blade.php -->
@extends('layouts.app')

@section('title', 'Military Gear - Tactical')
@section('brand_name', 'MILITARY GEAR [TACTICAL]')
@section('nav_class', 'bg-green-900 text-gray-200')
@section('body_class', 'bg-gray-200 text-gray-900')

@section('content')
    <h1 class="text-2xl font-bold bg-green-800 text-white p-2 mb-6 inline-block rounded">AVAILABLE EQUIPMENT</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($products as $product)
            <div class="bg-white border border-green-800 rounded shadow-sm p-4">
                <div class="h-40 bg-gray-300 mb-4 flex items-center justify-center text-4xl rounded">🛡️</div>
                <h2 class="font-bold text-lg text-green-900">{{ $product->name }}</h2>
                <div class="text-sm text-gray-600 mb-2">SKU: {{ $product->sku }}</div>
                <div class="flex justify-between items-center mt-4">
                    <span class="text-xl font-bold">${{ $product->price }}</span>
                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button class="bg-green-700 text-white px-4 py-2 rounded font-bold hover:bg-green-600">
                            BUY
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection