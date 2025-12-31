@extends('tenants.designer_hub.layouts.gadyuka')
@section('title', 'Cart')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-12">
    <h1 class="text-5xl font-display font-bold mb-12 uppercase">Cart_Content <span class="text-[#ff003c]">//</span></h1>

    @if(empty($cartItems))
        <div class="border-2 border-white bg-black p-12 text-center border-dashed">
            <p class="font-mono text-gray-400 text-xl mb-6">[CART_EMPTY]</p>
            <a href="{{ route('home') }}" class="bg-white text-black border-2 border-white px-8 py-3 font-display font-bold uppercase tracking-widest hover:bg-black hover:text-white transition-colors inline-block">
                [START_SHOPPING]
            </a>
        </div>
    @else
        <div class="flex flex-col lg:flex-row gap-12">
            <!-- Items List -->
            <div class="flex-1 space-y-6">
                @foreach($cartItems as $item)
                    <div class="border border-white bg-black p-4 flex gap-4 relative group">
                        <div class="w-24 h-32 border border-gray-700 shrink-0">
                            <img src="{{ $item['product']->cover_url }}" class="w-full h-full object-cover filter grayscale contrast-120 brightness-90 mix-blend-luminosity transition duration-300 group-hover:grayscale-0 group-hover:contrast-110 group-hover:mix-blend-normal">
                        </div>
                        
                        <div class="flex-1 flex flex-col justify-center">
                            <div class="flex justify-between">
                                <h3 class="font-display font-bold text-lg uppercase">{{ $item['product']->name }}</h3>
                                <div class="font-display font-bold text-lg text-[#ff003c]">${{ number_format($item['total'], 2) }}</div>
                            </div>
                            
                            <p class="font-mono text-xs text-gray-500 mt-1">Size: {{ $item['size'] }}</p>
                            <p class="font-mono text-xs text-gray-400 mt-1">${{ $item['price'] }} x {{ $item['quantity'] }}</p>
                            
                            <form action="{{ route('cart.remove', $item['row_id']) }}" method="POST" class="mt-4">
                                @csrf
                                <button class="text-xs font-mono uppercase text-gray-500 hover:text-[#ff003c] transition flex items-center gap-1">
                                    <span>[DEL] Remove</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Checkout Panel -->
            <div class="w-full lg:w-80">
                <div class="bg-black border-2 border-white p-6 shadow-[4px_4px_0px_0px_#ffffff] relative">
                    <h2 class="text-xl font-display font-bold mb-4 border-b border-gray-700 pb-2 uppercase">[ORDER_SUMMARY]</h2>
                    
                    <div class="space-y-2 font-mono text-sm mb-4">
                        <div class="flex justify-between text-gray-400">
                            <span>Subtotal</span>
                            <span class="font-mono font-bold">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        @if($discount > 0)
                            <div class="flex justify-between text-[#ff003c]">
                                <span>Discount</span>
                                <span class="font-mono">-${{ number_format($discount, 2) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-between mb-6 text-xl font-display font-bold border-t border-gray-700 pt-4 text-[#ff003c]">
                        <span>TOTAL</span>
                        <span class="font-mono">${{ number_format($total, 2) }}</span>
                    </div>

                    <!-- Promo -->
                    <form action="{{ route('cart.promo') }}" method="POST" class="mb-6 flex gap-2">
                        @csrf
                        <input type="text" name="code" placeholder="PROMO CODE" class="border-b border-gray-600 bg-transparent py-2 px-2 font-mono text-white focus:outline-none focus:border-[#ff003c] placeholder-gray-700 text-sm uppercase flex-1">
                        <button class="bg-white text-black font-mono text-xs px-4 py-2 border border-white hover:bg-black hover:text-white transition-colors">
                            Apply
                        </button>
                    </form>

                    <!-- Checkout Inputs -->
                    <form action="{{ route('checkout') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        @foreach(['customer_name' => 'Full Name', 'customer_phone' => 'Phone (+380...)', 'customer_email' => 'Email'] as $name => $label)
                        <div>
                            <label class="block font-mono text-[10px] text-gray-500 uppercase mb-1">{{ $label }}</label>
                            <input type="text" name="{{ $name }}" class="w-full border-b border-gray-600 bg-transparent py-2 font-mono text-white focus:outline-none focus:border-[#ff003c] placeholder-gray-700 text-sm" required>
                        </div>
                        @endforeach

                        <div>
                            <label class="block font-mono text-[10px] text-gray-500 uppercase mb-1">Shipping Method</label>
                            <select name="shipping_method" class="w-full border-b border-gray-600 bg-transparent py-2 font-mono text-white focus:outline-none focus:border-[#ff003c] text-sm">
                                <option value="nova_poshta" class="bg-black text-white">Nova Poshta</option>
                                <option value="courier" class="bg-black text-white">Courier Delivery</option>
                                <option value="pickup" class="bg-black text-white">Store Pickup</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block font-mono text-[10px] text-gray-500 uppercase mb-1">Address / Branch</label>
                            <textarea name="shipping_address" rows="2" class="w-full border border-gray-600 bg-transparent p-2 font-mono text-white focus:outline-none focus:border-[#ff003c] placeholder-gray-700 text-sm" required></textarea>
                        </div>

                        <button class="w-full bg-white text-black font-display font-bold py-4 text-lg border-2 border-white hover:bg-[#ff003c] hover:text-white transition-colors uppercase tracking-widest">
                            [CONFIRM_ORDER]
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection