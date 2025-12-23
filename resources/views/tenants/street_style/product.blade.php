<!-- FILE: resources/views/tenants/street_style/product.blade.php -->
@extends('layouts.app')

@section('title', $product->name)
@section('body_class', 'bg-black text-white font-sans')
@section('nav_class', 'bg-black border-b border-yellow-400')
@section('brand_name', 'STREET STYLE')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <a href="/" class="text-gray-400 hover:text-yellow-400 mb-6 inline-block font-bold">&larr; BACK TO DROP</a>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 bg-gray-800 p-8 border-2 border-yellow-400">
            <!-- Images Gallery -->
            <div x-data="{ activeImage: '{{ $product->cover_url }}' }">
                <div class="mb-4 border border-gray-700 h-96 overflow-hidden flex items-center justify-center bg-gray-900">
                    <img :src="activeImage" class="h-full w-full object-contain">
                </div>
                @if($product->images->count() > 1)
                    <div class="flex gap-2 overflow-x-auto pb-2">
                        @foreach($product->images as $img)
                            <button @click="activeImage = '{{ $img->url }}'" class="border border-gray-600 hover:border-yellow-400 w-20 h-20 flex-shrink-0">
                                <img src="{{ $img->url }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Details -->
            <div>
                <!-- CLOTHING LINE BADGE -->
                @if($product->clothingLine)
                    <a href="{{ route('home', ['line' => $product->clothingLine->slug]) }}" 
                       class="inline-block bg-yellow-400 text-black text-xs font-black px-2 py-1 mb-2 uppercase tracking-widest hover:bg-white transition">
                        Collection: {{ $product->clothingLine->name }}
                    </a>
                @endif

                <h1 class="text-4xl font-black uppercase mb-2">{{ $product->name }}</h1>
                <div class="text-3xl text-yellow-400 font-mono mb-6">${{ $product->price }}</div>
                
                <div class="mb-6 text-gray-300 leading-relaxed">
                    {{ $product->description }}
                </div>

                <div class="mb-4 border-t border-gray-700 pt-4">
                    <div class="flex flex-wrap gap-4 text-xs font-bold uppercase tracking-widest text-gray-500">
                        <div>Type: <span class="text-white">{{ $product->attributes['type'] ?? 'N/A' }}</span></div>
                        @if($product->categories->isNotEmpty())
                            <div>Category: 
                                @foreach($product->categories as $cat)
                                    <a href="{{ route('home', ['category' => $cat->slug]) }}" class="text-white hover:text-yellow-400">{{ $cat->name }}</a>{{ !$loop->last ? ',' : '' }}
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Variants Logic (Размеры) -->
                @if($product->variants->count() > 0)
                    <div class="mb-8">
                        <div class="text-gray-500 uppercase text-xs font-bold tracking-widest mb-2">Select Size:</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($product->variants as $variant)
                                @php $isOutOfStock = $variant->stock <= 0; @endphp
                                <label class="cursor-pointer {{ $isOutOfStock ? 'opacity-50 cursor-not-allowed' : '' }}">
                                    <input type="radio" name="size" value="{{ $variant->size }}" class="peer sr-only" {{ $isOutOfStock ? 'disabled' : '' }}>
                                    <div class="min-w-[40px] h-10 px-3 flex items-center justify-center border border-gray-600 {{ $isOutOfStock ? 'bg-gray-800 text-gray-600 border-gray-700' : 'peer-checked:bg-yellow-400 peer-checked:text-black peer-checked:border-yellow-400 hover:border-yellow-400 transition' }} font-bold relative">
                                        {{ $variant->size }}
                                        @if($isOutOfStock)
                                            <span class="absolute -top-2 -right-2 text-[10px] bg-red-600 text-white px-1 rounded">SOLD</span>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ route('cart.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button class="w-full bg-yellow-400 text-black font-black py-4 uppercase tracking-widest hover:bg-yellow-300 transition text-xl disabled:bg-gray-600 disabled:text-gray-400 disabled:cursor-not-allowed"
                            {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                        {{ $product->stock_quantity <= 0 ? 'SOLD OUT' : 'ADD TO CART' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection