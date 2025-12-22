<!-- FILE: resources/views/cart.blade.php -->
@extends('layouts.app')
@section('title', 'Shopping Cart')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Your Cart</h1>
    
    @if(count($cart) > 0)
        <div class="bg-white p-6 rounded shadow">
            @foreach($cart as $item)
                <div class="flex justify-between items-center border-b py-4">
                    <div>
                        <h3 class="font-bold">{{ $item['name'] }}</h3>
                        <p class="text-sm text-gray-500">Qty: {{ $item['quantity'] }}</p>
                    </div>
                    <div class="font-bold">${{ $item['price'] * $item['quantity'] }}</div>
                </div>
            @endforeach
            
            <div class="mt-6 flex justify-between items-center">
                <span class="text-xl font-bold">Total: ${{ $total }}</span>
                <form action="{{ route('checkout') }}" method="POST">
                    @csrf
                    <input type="email" name="email" placeholder="Enter email" required class="border p-2 rounded mr-2 text-black">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-500">
                        Checkout
                    </button>
                </form>
            </div>
        </div>
    @else
        <p>Cart is empty.</p>
        <a href="/" class="text-blue-500 underline mt-4 block">Back to Shop</a>
    @endif
@endsection

<!-- FILE: resources/views/success.blade.php -->
@extends('layouts.app')
@section('title', 'Order Success')

@section('content')
    <div class="text-center mt-12">
        <div class="text-6xl mb-4">✅</div>
        <h1 class="text-3xl font-bold mb-2">Order Placed!</h1>
        <p class="text-xl text-gray-600">Order #: <span class="font-mono font-bold">{{ $orderNumber }}</span></p>
        <p class="mt-4">Thank you for shopping at {{ $tenantId }}.</p>
        <a href="/" class="mt-8 inline-block bg-gray-800 text-white px-6 py-2 rounded">Continue Shopping</a>
    </div>
@endsection