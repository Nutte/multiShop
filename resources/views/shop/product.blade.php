<!-- FILE: resources/views/shop/product.blade.php -->
@extends('layouts.app')
@section('title', $product->name)

@section('content')
<div x-data="{ 
    selectedSize: null, 
    showSizeGuide: false,
    hasVariants: {{ $product->variants->count() > 0 ? 'true' : 'false' }}
}">
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 py-8">
        <!-- Image Section -->
        <div class="relative">
            <div class="aspect-[3/4] overflow-hidden theme-card relative group">
                <img src="{{ $product->cover_url }}" class="w-full h-full object-cover transition duration-700 group-hover:scale-105">
                
                @if($product->has_discount)
                    <div class="absolute top-4 right-4 theme-btn px-3 py-1 text-sm shadow-xl z-10">
                        SALE -{{ $product->discount_percentage }}%
                    </div>
                @endif
            </div>
        </div>

        <!-- Info Section -->
        <div class="flex flex-col justify-center">
            <div class="mb-2">
                <span class="theme-muted text-sm uppercase tracking-widest">{{ $product->clothingLine->name ?? 'Collection' }}</span>
                <h1 class="text-4xl md:text-5xl font-black uppercase tracking-tighter theme-skew theme-text mb-2">
                    {{ $product->name }}
                </h1>
                <p class="text-xs font-mono theme-muted">SKU: {{ $product->sku }}</p>
            </div>

            <div class="mb-6 border-b theme-border pb-6">
                <div class="text-3xl font-bold flex items-center gap-4">
                    @if($product->has_discount)
                        <span class="line-through theme-muted text-xl">${{ $product->price }}</span>
                        <span style="color: var(--color-primary)">${{ $product->sale_price }}</span>
                    @else
                        <span style="color: var(--color-primary)">${{ $product->price }}</span>
                    @endif
                </div>
            </div>

            <form action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="size" :value="selectedSize || 'One Size'">

                <!-- Size Selector (Только если есть варианты) -->
                @if($product->variants->count() > 0)
                    <div class="mb-8">
                        <div class="flex justify-between items-end mb-3">
                            <label class="font-bold text-sm uppercase theme-muted">Select Size</label>
                            
                            <!-- Trigger Size Guide -->
                            <button type="button" @click="showSizeGuide = true" class="text-xs underline hover:no-underline font-bold theme-text uppercase flex items-center gap-1">
                                <span>📏 Size Guide</span>
                            </button>
                        </div>
                        
                        <div class="flex flex-wrap gap-3">
                            @foreach($product->variants as $variant)
                                <button type="button" 
                                    @click="selectedSize = '{{ $variant->size }}'"
                                    class="h-12 w-12 flex items-center justify-center border-2 font-bold text-sm transition relative overflow-hidden"
                                    :class="selectedSize === '{{ $variant->size }}' 
                                        ? 'theme-bg theme-text border-[var(--color-primary)]' 
                                        : 'bg-transparent theme-border theme-text hover:border-[var(--color-primary)]'"
                                    {{ $variant->stock <= 0 ? 'disabled' : '' }}>
                                    
                                    {{ $variant->size }}

                                    <!-- Перечеркивание для Out of Stock -->
                                    @if($variant->stock <= 0)
                                        <div class="absolute inset-0 bg-gray-500 bg-opacity-20 cursor-not-allowed"></div>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="w-full h-0.5 bg-red-500 rotate-45"></div>
                                        </div>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                        <p class="mt-2 text-red-500 text-xs font-bold h-4" x-show="!selectedSize && hasVariants" x-cloak>
                            * Please select a size
                        </p>
                    </div>
                @endif

                <div class="mb-8">
                    <h3 class="font-bold text-sm uppercase theme-muted mb-2">Description</h3>
                    <div class="theme-text text-sm leading-relaxed opacity-80">
                        {{ $product->description ?? 'No description available for this item.' }}
                    </div>
                </div>

                <button class="theme-btn w-full py-4 text-xl shadow-lg hover:shadow-2xl transition disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="hasVariants && !selectedSize">
                    ADD TO CART
                </button>
                
                @if($product->variants->count() > 0)
                    <p class="text-center text-[10px] theme-muted uppercase mt-2 opacity-50">
                        Secure checkout powered by TriShop
                    </p>
                @endif
            </form>
        </div>
    </div>

    <!-- SIZE GUIDE MODAL (Universal) -->
    <div x-show="showSizeGuide" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80 backdrop-blur-sm p-4"
         x-transition.opacity>
        
        <div class="theme-card max-w-2xl w-full p-8 relative shadow-2xl bg-white theme-text" 
             @click.away="showSizeGuide = false">
            
            <button @click="showSizeGuide = false" class="absolute top-4 right-4 text-2xl font-bold hover:text-red-500">&times;</button>
            
            <h2 class="text-2xl font-black uppercase mb-6 theme-skew text-center">Size Guide</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-center border-collapse">
                    <thead>
                        <tr class="theme-bg theme-text text-white">
                            <th class="p-3 border theme-border">Size</th>
                            <th class="p-3 border theme-border">Chest (cm)</th>
                            <th class="p-3 border theme-border">Waist (cm)</th>
                            <th class="p-3 border theme-border">Hips (cm)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Примерные данные. Можно сделать динамическими в будущем -->
                        <tr>
                            <td class="p-3 border theme-border font-bold">XS</td>
                            <td class="p-3 border theme-border">82-87</td>
                            <td class="p-3 border theme-border">63-68</td>
                            <td class="p-3 border theme-border">88-93</td>
                        </tr>
                        <tr>
                            <td class="p-3 border theme-border font-bold">S</td>
                            <td class="p-3 border theme-border">88-93</td>
                            <td class="p-3 border theme-border">69-74</td>
                            <td class="p-3 border theme-border">94-99</td>
                        </tr>
                        <tr>
                            <td class="p-3 border theme-border font-bold">M</td>
                            <td class="p-3 border theme-border">94-99</td>
                            <td class="p-3 border theme-border">75-80</td>
                            <td class="p-3 border theme-border">100-105</td>
                        </tr>
                        <tr>
                            <td class="p-3 border theme-border font-bold">L</td>
                            <td class="p-3 border theme-border">100-105</td>
                            <td class="p-3 border theme-border">81-86</td>
                            <td class="p-3 border theme-border">106-111</td>
                        </tr>
                        <tr>
                            <td class="p-3 border theme-border font-bold">XL</td>
                            <td class="p-3 border theme-border">106-111</td>
                            <td class="p-3 border theme-border">87-92</td>
                            <td class="p-3 border theme-border">112-117</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-xs theme-muted text-center">
                * Measurements are in centimeters. Use as a general guide.
            </div>

            <div class="mt-6 text-center">
                <button @click="showSizeGuide = false" class="theme-btn px-6 py-2 text-sm">Close Guide</button>
            </div>
        </div>
    </div>

</div>
@endsection