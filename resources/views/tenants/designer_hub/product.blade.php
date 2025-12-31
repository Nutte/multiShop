@extends('tenants.designer_hub.layouts.gadyuka')
@section('title', $product->name)

@section('content')
<div x-data="{ 
    selectedSize: null, 
    showSizeGuide: false,
    hasVariants: {{ $product->variants->count() > 0 ? 'true' : 'false' }}
}">
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <div class="border-2 border-white bg-black p-4 grid grid-cols-1 lg:grid-cols-2 gap-8 relative">

            <!-- Image Section -->
            <div class="relative border-2 border-white group overflow-hidden">
                <div class="absolute top-2 left-2 z-10 bg-black/80 px-2 py-1 font-['JetBrains_Mono'] text-xs border border-white">IMG_{{ strtoupper(substr($product->sku, 0, 3)) }}.RAW</div>
                <img src="{{ $product->cover_url }}" class="w-full h-full object-cover filter grayscale contrast-120 brightness-90 mix-blend-luminosity transition duration-300 group-hover:grayscale-0 group-hover:contrast-110 group-hover:mix-blend-normal">

                <!-- Japanese Text Vertical -->
                <div class="absolute top-4 right-4 text-[#ff003c] font-black text-4xl opacity-80 pointer-events-none" style="writing-mode: vertical-rl;">
                    {{ strtoupper(substr($product->name, 0, 3)) }}
                </div>
                
                @if($product->has_discount)
                    <div class="absolute bottom-4 left-4 bg-[#ff003c] text-white font-bold font-['JetBrains_Mono'] px-3 py-1 border border-white">
                        SALE -{{ $product->discount_percentage }}%
                    </div>
                @endif
            </div>

            <!-- Info Section -->
            <div class="flex flex-col justify-center p-4">
                <nav class="font-['JetBrains_Mono'] text-xs mb-6 text-gray-500 border-b border-gray-800 pb-2">
                    INDEX // {{ $product->clothingLine->name ?? 'COLLECTION' }} // {{ strtoupper($product->category->name ?? 'GENERAL') }}
                </nav>

                <h1 class="text-5xl md:text-7xl font-['Space_Grotesk'] font-black uppercase leading-none mb-2 text-white">
                    {{ $product->name }} <span class="text-[#ff003c]">//</span>
                </h1>
                
                <div class="mb-8">
                    <p class="font-['JetBrains_Mono'] text-2xl mb-2 text-gray-300">
                        @if($product->has_discount)
                            <span class="line-through text-gray-500 text-xl mr-2">${{ $product->price }}</span>
                            <span class="text-[#ff003c]">${{ $product->sale_price }}</span>
                        @else
                            <span class="text-[#ff003c]">${{ $product->price }}</span>
                        @endif
                        <span class="text-xs align-top text-gray-600">INC. TAX</span>
                    </p>
                    <p class="font-['JetBrains_Mono'] text-xs text-gray-400">SKU: {{ $product->sku }}</p>
                </div>

                <!-- Description Box -->
                <div class="border border-white p-4 mb-8 bg-gray-900 relative">
                    <div class="absolute -top-3 left-4 bg-black px-2 font-['JetBrains_Mono'] text-xs text-[#ff003c] border border-white">DATA_LOG</div>
                    <p class="font-['JetBrains_Mono'] text-gray-300 text-sm leading-relaxed">
                        {{ $product->description ?? 'No description available for this item.' }}
                        <br><br>
                        > Material: 100% Cotton<br>
                        > Origin: Sector 7 (Portugal)
                    </p>
                </div>

                <form action="{{ route('cart.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="size" :value="selectedSize || 'One Size'">

                    <!-- Size Selector -->
                    @if($product->variants->count() > 0)
                        <div class="mb-8">
                            <div class="flex justify-between items-end mb-3">
                                <label class="font-['JetBrains_Mono'] font-bold uppercase mb-3 text-xs text-gray-400">Select_Size_Unit</label>
                                
                                <!-- Trigger Size Guide -->
                                <button type="button" @click="showSizeGuide = true" class="text-xs underline hover:no-underline font-['JetBrains_Mono'] text-[#ff003c] uppercase flex items-center gap-1">
                                    <span>📏 Size Guide</span>
                                </button>
                            </div>
                            
                            <div class="flex gap-4">
                                @foreach($product->variants as $variant)
                                    <button type="button" 
                                        @click="selectedSize = '{{ $variant->size }}'"
                                        class="w-12 h-12 flex items-center justify-center border border-gray-600 font-['Space_Grotesk'] font-bold text-lg bg-black text-gray-400 
                                                hover:border-white transition-all relative
                                                {{ $variant->stock <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        :class="selectedSize === '{{ $variant->size }}' 
                                            ? 'bg-white text-black border-white translate-x-1 translate-y-1' 
                                            : ''"
                                        {{ $variant->stock <= 0 ? 'disabled' : '' }}>
                                        
                                        {{ $variant->size }}

                                        @if($variant->stock <= 0)
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <div class="w-full h-0.5 bg-red-500 rotate-45"></div>
                                            </div>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                            <p class="mt-2 text-red-500 text-xs font-bold h-4" x-show="!selectedSize && hasVariants" x-cloak>
                                * SELECT_SIZE_REQUIRED
                            </p>
                        </div>
                    @endif

                    <button class="w-full bg-[#ff003c] text-white border-2 border-[#ff003c] py-4 text-xl font-['Space_Grotesk'] font-bold uppercase tracking-widest hover:bg-transparent hover:text-[#ff003c] transition-all shadow-[4px_4px_0px_0px_#ff003c] active:shadow-none active:translate-x-1 active:translate-y-1 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="hasVariants && !selectedSize">
                        [ADD_TO_CART]
                    </button>
                    
                    @if($product->variants->count() > 0)
                        <p class="text-center font-['JetBrains_Mono'] text-[10px] text-gray-500 uppercase mt-2">
                            Secure checkout powered by TriShop
                        </p>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- SIZE GUIDE MODAL -->
    <div x-show="showSizeGuide" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80 backdrop-blur-sm p-4"
         x-transition.opacity>
        
        <div class="bg-black border-2 border-white max-w-2xl w-full p-8 relative shadow-[4px_4px_0px_0px_#ffffff]" 
             @click.away="showSizeGuide = false">
            
            <button @click="showSizeGuide = false" class="absolute top-4 right-4 text-2xl font-bold hover:text-[#ff003c]">&times;</button>
            
            <h2 class="text-2xl font-['Space_Grotesk'] font-black uppercase mb-6 text-center">[SIZE_GUIDE]</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-center border-collapse font-['JetBrains_Mono']">
                    <thead>
                        <tr class="bg-black text-white border-b-2 border-white">
                            <th class="p-3 border border-white">Size</th>
                            <th class="p-3 border border-white">Chest (cm)</th>
                            <th class="p-3 border border-white">Waist (cm)</th>
                            <th class="p-3 border border-white">Hips (cm)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-700">
                            <td class="p-3 border border-gray-700 font-bold">XS</td>
                            <td class="p-3 border border-gray-700">82-87</td>
                            <td class="p-3 border border-gray-700">63-68</td>
                            <td class="p-3 border border-gray-700">88-93</td>
                        </tr>
                        <tr class="border-b border-gray-700">
                            <td class="p-3 border border-gray-700 font-bold">S</td>
                            <td class="p-3 border border-gray-700">88-93</td>
                            <td class="p-3 border border-gray-700">69-74</td>
                            <td class="p-3 border border-gray-700">94-99</td>
                        </tr>
                        <tr class="border-b border-gray-700">
                            <td class="p-3 border border-gray-700 font-bold">M</td>
                            <td class="p-3 border border-gray-700">94-99</td>
                            <td class="p-3 border border-gray-700">75-80</td>
                            <td class="p-3 border border-gray-700">100-105</td>
                        </tr>
                        <tr class="border-b border-gray-700">
                            <td class="p-3 border border-gray-700 font-bold">L</td>
                            <td class="p-3 border border-gray-700">100-105</td>
                            <td class="p-3 border border-gray-700">81-86</td>
                            <td class="p-3 border border-gray-700">106-111</td>
                        </tr>
                        <tr>
                            <td class="p-3 border border-gray-700 font-bold">XL</td>
                            <td class="p-3 border border-gray-700">106-111</td>
                            <td class="p-3 border border-gray-700">87-92</td>
                            <td class="p-3 border border-gray-700">112-117</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 font-['JetBrains_Mono'] text-xs text-gray-500 text-center">
                * Measurements are in centimeters. Use as a general guide.
            </div>

            <div class="mt-6 text-center">
                <button @click="showSizeGuide = false" class="bg-white text-black border-2 border-white px-6 py-2 font-['JetBrains_Mono'] text-sm hover:bg-black hover:text-white transition">
                    [CLOSE_GUIDE]
                </button>
            </div>
        </div>
    </div>

</div>
@endsection