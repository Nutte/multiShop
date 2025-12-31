@extends('tenants.military_gear.layouts.military')
@section('title', $product->name)

@section('content')
<div x-data="{ 
    selectedSize: null, 
    showSizeGuide: false,
    hasVariants: {{ $product->variants->count() > 0 ? 'true' : 'false' }}
}">
    
    <!-- Breadcrumb -->
    <div class="border-b border-military-gray bg-military-dark/50">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <nav class="flex text-xs font-mono text-military-text" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('home') }}" class="hover:text-white hover:underline">HOME</a></li>
                    <li><span class="text-military-accent">/</span></li>
                    <li><a href="{{ route('home', ['category' => $product->category->slug ?? '']) }}" class="hover:text-white hover:underline">{{ strtoupper($product->category->name ?? 'CLOTHING') }}</a></li>
                    <li><span class="text-military-accent">/</span></li>
                    <li class="text-white" aria-current="page">{{ strtoupper($product->name) }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16">
        <div class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
            <!-- Product Gallery (Left) -->
            <div class="product-gallery space-y-4">
                <div class="relative tech-border bg-military-dark aspect-[4/5] overflow-hidden group">
                    <div class="absolute top-0 left-0 w-full h-full bg-grid-pattern opacity-20 pointer-events-none z-10"></div>
                    <div class="corner-accent z-20"></div>
                    <img src="{{ $product->cover_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                    <div class="absolute bottom-4 left-4 z-20 bg-black/70 backdrop-blur border border-military-gray px-3 py-2 text-[10px] font-mono text-white">
                        <p>MAT: 100% COTTON</p>
                        <p>WGT: 240 GSM</p>
                    </div>
                </div>
                <div class="grid grid-cols-4 gap-4">
                    @foreach([1,2,3] as $i)
                    <button class="relative tech-border aspect-square overflow-hidden hover:border-military-accent transition-colors focus:ring-1 focus:ring-military-accent">
                        <img src="{{ $product->cover_url }}" class="w-full h-full object-cover opacity-70 hover:opacity-100">
                    </button>
                    @endforeach
                    <button class="relative tech-border aspect-square bg-military-dark flex items-center justify-center text-military-text hover:text-white hover:border-military-accent transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </button>
                </div>
            </div>
            
            <!-- Product Details (Right) -->
            <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
                <div class="border-b border-military-gray pb-6 mb-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h1 class="text-3xl md:text-4xl font-bold uppercase text-white tracking-wide">{{ $product->name }}</h1>
                            <p class="text-military-accent font-mono text-sm mt-1">SKU: {{ $product->sku }}</p>
                        </div>
                        <div class="text-right">
                            @if($product->has_discount)
                                <span class="text-military-accent font-mono font-bold text-2xl">{{ number_format($product->sale_price) }} ₴</span>
                                <span class="text-military-text text-sm line-through decoration-military-accent block">{{ number_format($product->price) }} ₴</span>
                            @else
                                <p class="text-2xl font-mono font-bold text-white">{{ number_format($product->price) }} ₴</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="prose prose-invert prose-sm mb-8 text-military-text font-mono leading-relaxed">
                    <p>{{ $product->description ?? 'Описание товара отсутствует.' }}</p>
                    <ul class="list-none pl-0 space-y-1 mt-4 border-l border-military-gray pl-4">
                        <li>> Материал: 100% Хлопок (Пенье)</li>
                        <li>> Плотность: 240 г/м²</li>
                        <li>> Крой: Oversize (Тактический крой плеча)</li>
                        <li>> Принт: Авторская иллюстрация</li>
                    </ul>
                </div>
                
                <form action="{{ route('cart.add') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    
                    @if($product->variants->count() > 0)
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-sm font-bold uppercase text-white">Выберите размер</label>
                            <button type="button" @click="showSizeGuide = true" class="text-xs font-mono text-military-text underline hover:text-military-accent">Таблица размеров [SIZE_CHART]</button>
                        </div>
                        <div class="grid grid-cols-5 gap-3">
                            @foreach($product->variants as $variant)
                            <label class="cursor-pointer group">
                                <input type="radio" name="size" value="{{ $variant->size }}" class="peer sr-only" 
                                       @click="selectedSize = '{{ $variant->size }}'" 
                                       {{ $variant->stock <= 0 ? 'disabled' : '' }}>
                                <div class="h-12 w-full flex items-center justify-center border border-military-gray bg-military-dark text-military-text font-mono text-sm peer-checked:bg-white peer-checked:text-black peer-checked:border-white hover:border-military-accent hover:text-white transition-all {{ $variant->stock <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                                    {{ $variant->size }}
                                    @if($variant->stock <= 0)
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="w-full h-0.5 bg-red-500 rotate-45"></div>
                                        </div>
                                    @endif
                                </div>
                            </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-red-500 text-xs font-bold h-4" x-show="!selectedSize && hasVariants" x-cloak>
                            * Выберите размер
                        </p>
                    </div>
                    @endif
                    
                    <div class="flex gap-4 pt-6 border-t border-military-gray">
                        <button type="submit" 
                                class="flex-1 bg-military-accent text-black font-bold uppercase text-lg h-14 flex items-center justify-center gap-2 hover:bg-orange-500 transition-colors relative overflow-hidden group"
                                :disabled="hasVariants && !selectedSize">
                            <span class="relative z-10">В корзину</span>
                            <svg class="w-5 h-5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <div class="absolute inset-0 bg-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-200 opacity-20"></div>
                        </button>
                        <button type="button" class="w-14 h-14 border border-military-gray flex items-center justify-center text-military-text hover:text-white hover:border-military-accent transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </button>
                    </div>
                </form>
                
                <div class="mt-8 border border-military-gray bg-black p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-xs font-mono text-green-500 uppercase">In Stock / Ready to ship</span>
                    </div>
                    <p class="text-[10px] font-mono text-military-text uppercase">
                        Shipping: Nova Poshta (1-3 days)<br>
                        Returns: 14 days tactical warranty<br>
                        Secure encryption: SSL-256
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- SIZE GUIDE MODAL -->
    <div x-show="showSizeGuide" style="display: none;" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80 backdrop-blur-sm p-4"
         x-transition.opacity>
        
        <div class="bg-military-dark border border-military-gray max-w-2xl w-full p-8 relative" 
             @click.away="showSizeGuide = false">
            
            <button @click="showSizeGuide = false" class="absolute top-4 right-4 text-2xl font-bold text-military-text hover:text-white">&times;</button>
            
            <h2 class="text-2xl font-bold uppercase mb-6 text-white text-center">Size Guide</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-center">
                    <thead>
                        <tr class="bg-military-black text-white">
                            <th class="p-3 border border-military-gray">Size</th>
                            <th class="p-3 border border-military-gray">Chest (cm)</th>
                            <th class="p-3 border border-military-gray">Waist (cm)</th>
                            <th class="p-3 border border-military-gray">Hips (cm)</th>
                        </tr>
                    </thead>
                    <tbody class="text-military-text">
                        <tr>
                            <td class="p-3 border border-military-gray font-bold">XS</td>
                            <td class="p-3 border border-military-gray">82-87</td>
                            <td class="p-3 border border-military-gray">63-68</td>
                            <td class="p-3 border border-military-gray">88-93</td>
                        </tr>
                        <tr>
                            <td class="p-3 border border-military-gray font-bold">S</td>
                            <td class="p-3 border border-military-gray">88-93</td>
                            <td class="p-3 border border-military-gray">69-74</td>
                            <td class="p-3 border border-military-gray">94-99</td>
                        </tr>
                        <tr>
                            <td class="p-3 border border-military-gray font-bold">M</td>
                            <td class="p-3 border border-military-gray">94-99</td>
                            <td class="p-3 border border-military-gray">75-80</td>
                            <td class="p-3 border border-military-gray">100-105</td>
                        </tr>
                        <tr>
                            <td class="p-3 border border-military-gray font-bold">L</td>
                            <td class="p-3 border border-military-gray">100-105</td>
                            <td class="p-3 border border-military-gray">81-86</td>
                            <td class="p-3 border border-military-gray">106-111</td>
                        </tr>
                        <tr>
                            <td class="p-3 border border-military-gray font-bold">XL</td>
                            <td class="p-3 border border-military-gray">106-111</td>
                            <td class="p-3 border border-military-gray">87-92</td>
                            <td class="p-3 border border-military-gray">112-117</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-xs text-military-text text-center">
                * Measurements are in centimeters. Use as a general guide.
            </div>

            <div class="mt-6 text-center">
                <button @click="showSizeGuide = false" class="px-6 py-2 bg-military-accent text-black font-bold uppercase text-sm">Close Guide</button>
            </div>
        </div>
    </div>
</div>
@endsection