@extends('tenants.military_gear.layouts.military')
@section('title', 'Supply Crate')

@php
    $cartItems = session('cart_military_gear', []);
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <div class="flex items-center gap-4 mb-8 border-b border-military-gray pb-4">
        <h1 class="text-3xl font-bold uppercase text-white">Supply Crate</h1>
        <span class="bg-military-accent text-black font-mono text-xs px-2 py-1 font-bold">{{ count($cartItems) }} ITEMS</span>
    </div>

    @if(empty($cartItems))
        <div class="tech-border bg-military-dark p-12 text-center">
            <p class="text-military-text text-xl mb-6">Your cart is currently empty.</p>
            <a href="{{ route('home') }}" class="px-8 py-3 bg-military-accent text-black font-bold uppercase inline-block">Start Shopping</a>
        </div>
    @else
        <div class="lg:grid lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-8 space-y-6">
                @foreach($cartItems as $itemId => $item)
                <div class="tech-border bg-military-dark p-4 flex flex-col sm:flex-row gap-6 relative group">
                    <div class="corner-accent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="w-full sm:w-32 aspect-[3/4] bg-black overflow-hidden flex-shrink-0">
                        <img src="{{ $item['product']->cover_url }}" class="w-full h-full object-cover opacity-80">
                    </div>
                    <div class="flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-white uppercase text-lg">{{ $item['product']->name }}</h3>
                                <p class="font-mono text-white font-bold">{{ number_format($item['total']) }} ₴</p>
                            </div>
                            <p class="text-xs font-mono text-military-text mt-1">SIZE: {{ $item['size'] }} // COLOR: BLACK</p>
                            <p class="text-[10px] font-mono text-zinc-600 mt-2">SKU: {{ $item['product']->sku }}</p>
                        </div>
                        <div class="flex justify-between items-end mt-4">
                            <div class="flex items-center border border-military-gray">
                                <form action="{{ route('cart.update', $itemId) }}" method="POST" class="flex">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" name="action" value="decrease" class="w-8 h-8 flex items-center justify-center text-white hover:bg-military-gray transition-colors">-</button>
                                    <input type="text" value="{{ $item['quantity'] }}" class="w-10 h-8 bg-transparent text-center text-white font-mono text-sm focus:outline-none" readonly>
                                    <button type="submit" name="action" value="increase" class="w-8 h-8 flex items-center justify-center text-white hover:bg-military-gray transition-colors">+</button>
                                </form>
                            </div>
                            <form action="{{ route('cart.remove', $itemId) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-mono text-red-500 hover:text-red-400 uppercase tracking-wider hover:underline decoration-red-500 underline-offset-4">
                                    [DROP ITEM]
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Summary -->
            <div class="lg:col-span-4 mt-8 lg:mt-0">
                <div class="tech-border bg-military-dark/50 p-6 sticky top-24">
                    <h2 class="font-bold text-white uppercase mb-6 flex items-center gap-2">
                        <span class="w-2 h-2 bg-military-accent"></span> Summary
                    </h2>
                    
                    @php
                        $subtotal = array_sum(array_column($cartItems, 'total'));
                        $total = $subtotal;
                    @endphp
                    
                    <div class="space-y-4 text-sm font-mono text-military-text">
                        <div class="flex justify-between">
                            <span>SUBTOTAL</span>
                            <span class="text-white">{{ number_format($subtotal) }} ₴</span>
                        </div>
                        <div class="flex justify-between">
                            <span>SHIPPING</span>
                            <span class="text-zinc-500">[CALCULATED AT CHECKOUT]</span>
                        </div>
                        <div class="flex justify-between">
                            <span>TAX (INCLUDED)</span>
                            <span class="text-white">0 ₴</span>
                        </div>
                    </div>
                    
                    <div class="border-t border-military-gray my-6 pt-4">
                        <div class="flex justify-between items-end">
                            <span class="font-bold text-white text-lg">TOTAL</span>
                            <span class="font-bold text-military-accent text-2xl font-mono">{{ number_format($total) }} ₴</span>
                        </div>
                    </div>
                    
                    <form action="{{ route('checkout') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        @foreach(['customer_name' => 'Full Name', 'customer_phone' => 'Phone (+380...)', 'customer_email' => 'Email'] as $name => $label)
                        <div>
                            <label class="block text-[10px] font-bold uppercase mb-1 text-military-text">{{ $label }}</label>
                            <input type="text" name="{{ $name }}" class="w-full bg-black/50 border border-military-gray text-white px-3 py-2 text-sm focus:border-military-accent focus:outline-none font-mono" required>
                        </div>
                        @endforeach

                        <div>
                            <label class="block text-[10px] font-bold uppercase mb-1 text-military-text">Shipping Method</label>
                            <select name="shipping_method" class="w-full bg-black/50 border border-military-gray text-white px-3 py-2 text-sm focus:border-military-accent focus:outline-none font-mono">
                                <option value="nova_poshta">Nova Poshta</option>
                                <option value="courier">Courier Delivery</option>
                                <option value="pickup">Store Pickup</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold uppercase mb-1 text-military-text">Address / Branch</label>
                            <textarea name="shipping_address" rows="2" class="w-full bg-black/50 border border-military-gray text-white px-3 py-2 text-sm focus:border-military-accent focus:outline-none font-mono" required></textarea>
                        </div>

                        <button class="w-full bg-white text-black font-bold uppercase py-4 hover:bg-military-accent transition-colors mt-4">
                            Proceed to Checkout ->
                        </button>
                    </form>
                    
                    <p class="text-[10px] text-center text-zinc-600 mt-4 font-mono">
                        SECURE ENCRYPTED TRANSACTION
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection