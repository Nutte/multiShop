<!-- FILE: resources/views/cart/index.blade.php -->
@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>

        @if(empty($cartItems))
            <div class="bg-white p-12 text-center rounded shadow">
                <p class="text-xl text-gray-500 mb-4">Your cart is empty.</p>
                <a href="{{ route('home') }}" class="inline-block bg-black text-white px-6 py-3 rounded font-bold hover:bg-gray-800">
                    Start Shopping
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- CART ITEMS LIST -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach($cartItems as $item)
                        <div class="bg-white p-4 rounded shadow flex gap-4 items-center">
                            <img src="{{ $item['product']->cover_url }}" class="w-20 h-24 object-cover rounded">
                            
                            <div class="flex-1">
                                <h3 class="font-bold text-lg">{{ $item['product']->name }}</h3>
                                <p class="text-sm text-gray-500">Size: {{ $item['size'] }}</p>
                                <div class="mt-1 font-mono">${{ $item['price'] }} x {{ $item['quantity'] }}</div>
                            </div>
                            
                            <div class="text-right">
                                <div class="font-bold text-lg">${{ number_format($item['total'], 2) }}</div>
                                <form action="{{ route('cart.remove', $item['row_id']) }}" method="POST" class="mt-2">
                                    @csrf
                                    <button class="text-red-500 text-sm hover:underline">Remove</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- ORDER SUMMARY & CHECKOUT FORM -->
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded shadow sticky top-4">
                        <h2 class="text-xl font-bold mb-4 border-b pb-2">Order Summary</h2>
                        
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-mono">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        
                        @if($discount > 0)
                            <div class="flex justify-between mb-2 text-green-600 font-bold">
                                <span>Discount ({{ $promoCode }})</span>
                                <span class="font-mono">-${{ number_format($discount, 2) }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between mb-6 text-xl font-bold border-t pt-2">
                            <span>Total</span>
                            <span class="font-mono">${{ number_format($total, 2) }}</span>
                        </div>

                        <!-- PROMO CODE INPUT -->
                        <form action="{{ route('cart.promo') }}" method="POST" class="mb-6 flex gap-2">
                            @csrf
                            <input type="text" name="code" placeholder="Promo Code" class="flex-1 border p-2 rounded uppercase">
                            <button class="bg-gray-200 px-4 rounded font-bold hover:bg-gray-300">Apply</button>
                        </form>

                        <hr class="mb-6">

                        <!-- CHECKOUT FORM -->
                        <h3 class="font-bold text-lg mb-4">Shipping Details</h3>
                        <form action="{{ route('checkout') }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label class="block text-xs font-bold uppercase mb-1">Full Name</label>
                                <input type="text" name="customer_name" class="w-full border p-2 rounded" required placeholder="John Doe">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase mb-1">Phone</label>
                                <input type="text" name="customer_phone" class="w-full border p-2 rounded" required placeholder="+1 234 567 890">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase mb-1">Email</label>
                                <input type="email" name="customer_email" class="w-full border p-2 rounded" required placeholder="john@example.com">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase mb-1">Shipping Method</label>
                                <select name="shipping_method" class="w-full border p-2 rounded bg-gray-50" required>
                                    <option value="nova_poshta">Nova Poshta (Post Office)</option>
                                    <option value="courier">Courier Delivery</option>
                                    <option value="pickup">Store Pickup</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase mb-1">Delivery Address</label>
                                <textarea name="shipping_address" rows="2" class="w-full border p-2 rounded" required placeholder="City, Street, Building..."></textarea>
                            </div>

                            <div class="bg-yellow-50 p-3 rounded text-xs text-yellow-800 mb-4">
                                ℹ️ Payment Method: <b>Cash on Delivery (COD)</b> only.
                            </div>

                            <button class="w-full bg-green-600 text-white font-bold py-4 rounded hover:bg-green-500 shadow-lg text-lg">
                                PLACE ORDER
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection