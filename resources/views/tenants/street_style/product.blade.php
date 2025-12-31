<!-- FILE: resources/views/tenants/street_style/product.blade.php -->
@extends('tenants.street_style.layouts.artefact')
@section('title', $product->name . ' - ARTEFACT.ROOM')

@section('content')
<main class="w-full pt-24 bg-[#0a0a0a]">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-16 pb-24">
        <div class="relative">
            <div class="paper-block p-4 rotate-1">
                <img src="{{ $product->cover_url }}" class="w-full h-auto filter grayscale contrast-110">
            </div>
        </div>
        <div class="text-white relative">
            <h1 class="font-display font-black text-6xl uppercase mb-2">{{ $product->name }}</h1>
            <div class="font-sketch text-3xl text-blue-500 mb-8 rotate-[-1deg]">"{{ Str::limit($product->description, 50) ?? 'Urban essential' }}"</div>
            <div class="bg-[#1a1a1a] border-l-4 border-blue-600 p-6 mb-8 text-gray-300 font-tech text-sm leading-relaxed">
                {{ $product->clothingLine->description ?? 'Heavy cotton. Hand printed. Unique piece.' }}
            </div>

            <!-- SIZE SELECTOR -->
            @if($product->variants->count() > 0)
            <div class="mb-8 border-t border-gray-800 pt-6">
                <label class="font-spray text-xl text-[#ccff00] mb-4 block transform -rotate-1">Select Size_</label>
                <div class="flex gap-4">
                    @foreach($product->variants as $variant)
                    <label class="cursor-pointer group">
                        <input type="radio" name="size" value="{{ $variant->size }}" class="peer sr-only" 
                               {{ $variant->stock <= 0 ? 'disabled' : '' }} {{ $loop->first ? 'checked' : '' }}>
                        <div class="w-12 h-12 border-2 border-white text-white font-tech flex items-center justify-center 
                                    peer-checked:bg-[#ccff00] peer-checked:text-black peer-checked:border-[#ccff00] 
                                    transition group-hover:border-[#ccff00]
                                    {{ $variant->stock <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                            {{ $variant->size }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- PRICE -->
            <div class="mb-8">
                <div class="text-3xl font-bold flex items-center gap-4">
                    @if($product->has_discount)
                        <span class="line-through text-gray-500 text-xl">₴{{ number_format($product->price * 40, 0) }}</span>
                        <span class="text-[#ccff00]">₴{{ number_format($product->sale_price * 40, 0) }}</span>
                    @else
                        <span class="text-[#ccff00]">₴{{ number_format($product->price * 40, 0) }}</span>
                    @endif
                </div>
            </div>

            <!-- ADD TO CART FORM -->
            <form action="{{ route('cart.add') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="size" id="selectedSize" value="{{ $product->variants->first()->size ?? 'One Size' }}">
                
                <button type="submit" 
                        class="bg-white text-black font-display font-black text-xl uppercase px-12 py-4 
                               hover:bg-pink-500 hover:text-white transition transform hover:scale-105 
                               shadow-[6px_6px_0px_#ccff00] w-full">
                    Add to Bag
                </button>
                
                @if($product->variants->count() > 0)
                <p class="text-center text-[10px] text-gray-500 uppercase mt-2 opacity-50">
                    * Select size before adding to cart
                </p>
                @endif
            </form>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sizeRadios = document.querySelectorAll('input[name="size"]');
        const selectedSizeInput = document.getElementById('selectedSize');
        
        sizeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    selectedSizeInput.value = this.value;
                }
            });
        });
    });
</script>
@endsection