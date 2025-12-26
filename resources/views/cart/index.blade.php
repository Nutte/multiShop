<!-- FILE: resources/views/cart/index.blade.php -->
@extends('layouts.app')
@section('title', 'Cart')

@section('content')
    <h1 class="text-3xl font-bold uppercase mb-8 theme-skew theme-text text-center md:text-left">Your Selection</h1>

    @if(empty($cartItems))
        <div class="theme-card p-12 text-center border-dashed">
            <p class="theme-muted text-xl mb-6">Your cart is currently empty.</p>
            <a href="{{ route('home') }}" class="theme-btn px-8 py-3 inline-block">Start Shopping</a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Items List -->
            <div class="lg:col-span-2 space-y-4">
                @foreach($cartItems as $item)
                    <div class="theme-card p-4 flex gap-4 items-start relative overflow-hidden group">
                        <div class="w-24 h-32 flex-shrink-0 bg-gray-100 theme-border border">
                            <img src="{{ $item['product']->cover_url }}" class="w-full h-full object-cover">
                        </div>
                        
                        <div class="flex-1">
                            <div class="flex justify-between">
                                <h3 class="font-bold text-lg theme-text">{{ $item['product']->name }}</h3>
                                <div class="font-bold text-lg theme-text">${{ number_format($item['total'], 2) }}</div>
                            </div>
                            
                            <p class="theme-muted text-sm mt-1">Size: {{ $item['size'] }}</p>
                            <p class="theme-muted text-sm font-mono mt-1">${{ $item['price'] }} x {{ $item['quantity'] }}</p>
                            
                            <form action="{{ route('cart.remove', $item['row_id']) }}" method="POST" class="mt-4">
                                @csrf
                                <button class="text-xs font-bold uppercase text-red-500 hover:text-red-700 transition flex items-center gap-1">
                                    <span>&times; Remove</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Checkout Panel -->
            <div class="lg:col-span-1">
                <div class="theme-card p-6 sticky top-4 shadow-lg">
                    <h2 class="text-xl font-bold mb-4 border-b theme-border pb-2 uppercase theme-text">Order Summary</h2>
                    
                    <div class="space-y-2 mb-4 text-sm">
                        <div class="flex justify-between theme-text">
                            <span>Subtotal</span>
                            <span class="font-mono">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if($discount > 0)
                            <div class="flex justify-between" style="color: var(--color-primary)">
                                <span>Discount</span>
                                <span class="font-mono">-${{ number_format($discount, 2) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-between mb-6 text-xl font-bold border-t theme-border pt-4 theme-text">
                        <span>Total</span>
                        <span class="font-mono" style="color: var(--color-primary)">${{ number_format($total, 2) }}</span>
                    </div>

                    <!-- Promo -->
                    <form action="{{ route('cart.promo') }}" method="POST" class="mb-6 flex gap-2">
                        @csrf
                        <input type="text" name="code" placeholder="PROMO CODE" class="theme-input p-2 w-full uppercase text-sm font-bold">
                        <button class="theme-btn px-4 text-sm">Apply</button>
                    </form>

                    <!-- Checkout Inputs -->
                    <form action="{{ route('checkout') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        @foreach(['customer_name' => 'Full Name', 'customer_phone' => 'Phone (+380...)', 'customer_email' => 'Email'] as $name => $label)
                        <div>
                            <label class="block text-[10px] font-bold uppercase mb-1 theme-muted">{{ $label }}</label>
                            <input type="text" name="{{ $name }}" class="theme-input w-full p-3 font-bold text-sm" required>
                        </div>
                        @endforeach

                        <div>
                            <label class="block text-[10px] font-bold uppercase mb-1 theme-muted">Shipping Method</label>
                            <select name="shipping_method" class="theme-input w-full p-3 bg-transparent font-bold text-sm">
                                <option value="nova_poshta" class="text-black">Nova Poshta</option>
                                <option value="courier" class="text-black">Courier Delivery</option>
                                <option value="pickup" class="text-black">Store Pickup</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold uppercase mb-1 theme-muted">Address / Branch</label>
                            <textarea name="shipping_address" rows="2" class="theme-input w-full p-3 font-bold text-sm" required></textarea>
                        </div>

                        <button class="theme-btn w-full py-4 text-lg mt-4 shadow-xl hover:shadow-2xl transition-shadow">
                            CONFIRM ORDER
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection