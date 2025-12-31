<!-- FILE: resources/views/tenants/street_style/cart.blade.php -->
@extends('tenants.street_style.layouts.artefact')
@section('title', 'Cart - ARTEFACT.ROOM')

@section('content')
<main class="w-full pt-24 pb-20">
    <div class="max-w-4xl mx-auto px-6">
        <h2 class="text-4xl font-display font-bold text-white uppercase mb-8 flex items-center gap-4">
            Manifest <span class="font-sketch text-blue-500 text-3xl lowercase">#{{ str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) }}</span>
        </h2>
        <div class="paper-block p-8 md:p-12 relative rotate-[0.5deg]">
            <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-32 h-12 bg-gray-800 rounded-t-lg border-t border-gray-600 shadow-xl z-20"></div>
            <div class="grid grid-cols-12 gap-4 border-b-2 border-black/80 pb-2 mb-6 font-tech text-xs font-bold uppercase tracking-wider text-gray-500">
                <div class="col-span-6 md:col-span-7">Item / Specs</div>
                <div class="col-span-3 md:col-span-2 text-center">Qty</div>
                <div class="col-span-3 text-right">Total</div>
            </div>
            
            @forelse($cartItems as $item)
            <div class="grid grid-cols-12 gap-4 items-center mb-8 pb-8 border-b border-dashed border-gray-400 relative group">
                <div class="col-span-6 md:col-span-7 flex gap-4">
                    <div class="w-20 h-24 bg-gray-200 border border-black p-1 rotate-[-2deg] shadow-sm hidden md:block">
                        <img src="{{ $item['product']->cover_url }}" class="w-full h-full object-cover grayscale">
                    </div>
                    <div>
                        <h3 class="font-bold text-lg font-display uppercase">{{ $item['product']->name }}</h3>
                        <p class="font-tech text-[10px] text-gray-500">SIZE: {{ $item['size'] }} // BLACK</p>
                        <form action="{{ route('cart.remove', $item['row_id']) }}" method="POST" class="mt-2">
                            @csrf
                            <button class="text-[10px] font-bold text-red-500 uppercase hover:text-red-700">[ Remove ]</button>
                        </form>
                    </div>
                </div>
                <div class="col-span-3 md:col-span-2 flex justify-center">
                    <form action="{{ route('cart.update', $item['row_id']) }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" 
                               class="w-12 text-center font-sketch text-2xl bg-transparent border-b border-black outline-none">
                        <button type="submit" class="text-[10px] font-bold uppercase opacity-0 group-hover:opacity-100 transition">Update</button>
                    </form>
                </div>
                <div class="col-span-3 text-right font-tech font-bold text-lg">₴{{ number_format($item['total'] * 40, 0) }}</div>
            </div>
            @empty
            <div class="text-center py-12">
                <p class="font-tech text-gray-500 mb-6">Your cart is empty</p>
                <a href="{{ route('home') }}" class="font-spray text-xl text-pink-600 underline decoration-wavy">Go shopping -></a>
            </div>
            @endforelse

            @if(!empty($cartItems))
            <div class="flex flex-col items-end gap-2 mt-12">
                <div class="flex justify-between w-full md:w-1/2 font-display text-3xl font-bold border-t-2 border-black pt-4 mt-2">
                    <span>Total:</span>
                    <span>₴{{ number_format($total * 40, 0) }}</span>
                </div>
                
                <!-- Checkout Form -->
                <form action="{{ route('checkout') }}" method="POST" class="mt-8 w-full md:w-auto">
                    @csrf
                    
                    <!-- Customer Info -->
                    <div class="mb-6 p-4 border border-gray-700 bg-black/50">
                        <h3 class="font-tech text-sm uppercase mb-3 text-gray-400">Customer Details</h3>
                        <div class="space-y-3">
                            <input type="text" name="customer_name" placeholder="FULL NAME" required 
                                   class="w-full p-2 bg-transparent border-b border-gray-700 text-white placeholder-gray-500">
                            <input type="tel" name="customer_phone" placeholder="PHONE (+380...)" required 
                                   class="w-full p-2 bg-transparent border-b border-gray-700 text-white placeholder-gray-500">
                            <input type="email" name="customer_email" placeholder="EMAIL" 
                                   class="w-full p-2 bg-transparent border-b border-gray-700 text-white placeholder-gray-500">
                        </div>
                    </div>
                    
                    <button class="bg-black text-white font-tech text-sm uppercase px-8 py-4 w-full hover:bg-pink-500 transition shadow-[4px_4px_0px_#ccff00]">
                        Proceed to Checkout ->
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</main>
@endsection