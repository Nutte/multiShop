<!-- FILE: resources/views/shop/products.blade.php -->
@extends('tenants.designer_hub.layouts.gadyuka')

@section('title', 'Catalog - GADYUKA.BRAND')

@section('content')
<div x-data="{ filtersOpen: false }" class="max-w-[1400px] mx-auto px-4 sm:px-6 py-8">
    
    <div class="mb-12 border-b-2 border-white pb-6 flex flex-col md:flex-row justify-between items-end gap-4">
        <div>
            <h1 class="text-5xl md:text-7xl font-display font-black uppercase leading-none">Catalog <span class="text-brand-accent text-3xl align-top">カタログ</span></h1>
            <p class="font-mono text-xs text-gray-400 mt-2">/// INDEX_ALL_ITEMS /// DATABASE_V2</p>
            <p class="font-mono text-xs text-gray-500 mt-1">
                <span class="font-bold text-white">{{ $stats['total'] }}</span> items • 
                <span class="text-green-400 font-bold">{{ $stats['new'] }}</span> new • 
                <span class="text-red-400 font-bold">{{ $stats['discount'] }}</span> on sale
            </p>
        </div>
        <!-- Mobile Filter Toggle -->
        <button @click="filtersOpen = !filtersOpen" class="md:hidden w-full bg-white text-black font-bold font-mono py-3 uppercase border-2 border-white hover:bg-brand-accent hover:text-white transition-colors">
            [Filter_Parameters] <span x-text="filtersOpen ? '[-]' : '[+]'"></span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
        
        <!-- FILTERS SIDEBAR -->
        <aside class="md:col-span-3 space-y-8" :class="{'hidden md:block': !filtersOpen, 'block': filtersOpen}">
            
            <!-- Search -->
            <div class="border-2 border-white p-1 bg-black">
                <form method="GET" action="{{ route('shop.products') }}">
                    <input type="text" name="search" placeholder="SEARCH_DATABASE..." value="{{ request('search') }}"
                           class="w-full bg-gray-900 text-white font-mono text-xs p-3 focus:outline-none focus:bg-black placeholder-gray-600">
                </form>
            </div>

            <!-- Categories -->
            <div class="border-2 border-white p-4 bg-black relative">
                <div class="absolute -top-3 left-2 bg-black px-2 font-mono text-xs font-bold text-brand-accent border border-white">CATEGORY</div>
                <div class="space-y-3 mt-2">
                    @foreach($categories as $category)
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="category[]" value="{{ $category->slug }}" 
                               class="sr-only checkbox-manga" 
                               @if(in_array($category->slug, (array)request('category', []))) checked @endif
                               onchange="this.form.submit()">
                        <div class="w-4 h-4 border border-white group-hover:border-brand-accent"></div>
                        <span class="font-mono text-xs uppercase group-hover:text-brand-accent">{{ $category->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Size -->
            <div class="border-2 border-white p-4 bg-black relative">
                <div class="absolute -top-3 left-2 bg-black px-2 font-mono text-xs font-bold text-brand-accent border border-white">SIZE_UNIT</div>
                <div class="grid grid-cols-4 gap-2 mt-2">
                    @foreach($sizes as $size)
                    <button type="button" onclick="window.location='{{ request()->fullUrlWithQuery(['size' => $size]) }}'" 
                            class="border {{ request('size') == $size ? 'border-white bg-white text-black' : 'border-gray-600 hover:border-white hover:bg-white hover:text-black' }} py-2 font-mono text-xs font-bold transition-colors">
                        {{ $size }}
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Price -->
            <div class="border-2 border-white p-4 bg-black relative">
                <div class="absolute -top-3 left-2 bg-black px-2 font-mono text-xs font-bold text-brand-accent border border-white">PRICE_RANGE</div>
                <form method="GET" action="{{ route('shop.products') }}" class="mt-4">
                    <input type="range" name="max_price" min="0" max="500" value="{{ request('max_price', 500) }}"
                           class="w-full accent-brand-accent h-1 bg-gray-700 rounded-none appearance-none cursor-pointer"
                           onchange="this.form.submit()">
                    <div class="flex justify-between font-mono text-xs mt-2 text-gray-400">
                        <span>€{{ request('min_price', 0) }}</span>
                        <span>€{{ request('max_price', 500) }}</span>
                    </div>
                </form>
            </div>

            <!-- Collection -->
            <div class="border-2 border-white p-4 bg-black relative">
                <div class="absolute -top-3 left-2 bg-black px-2 font-mono text-xs font-bold text-brand-accent border border-white">LINE / COLLECTION</div>
                <div class="space-y-3 mt-2">
                    @foreach($clothingLines as $line)
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="line[]" value="{{ $line->slug }}"
                               class="sr-only checkbox-manga"
                               @if(in_array($line->slug, (array)request('line', []))) checked @endif
                               onchange="this.form.submit()">
                        <div class="w-4 h-4 border border-white group-hover:border-brand-accent"></div>
                        <span class="font-mono text-xs uppercase group-hover:text-brand-accent">{{ $line->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="border-2 border-white p-4 bg-black relative">
                <div class="absolute -top-3 left-2 bg-black px-2 font-mono text-xs font-bold text-brand-accent border border-white">QUICK_SELECT</div>
                <div class="space-y-3 mt-2">
                    <a href="{{ route('shop.products') }}" 
                       class="block px-3 py-2 border border-white text-white font-mono text-xs uppercase hover:bg-white hover:text-black transition-colors text-center">
                        ALL_ITEMS
                    </a>
                    <a href="{{ route('shop.products', ['preset' => 'new']) }}" 
                       class="block px-3 py-2 border border-blue-400 text-blue-400 font-mono text-xs uppercase hover:bg-blue-400 hover:text-black transition-colors text-center">
                        🆕 NEW
                    </a>
                    <a href="{{ route('shop.products', ['preset' => 'discount']) }}" 
                       class="block px-3 py-2 border border-red-400 text-red-400 font-mono text-xs uppercase hover:bg-red-400 hover:text-black transition-colors text-center">
                        🔥 DISCOUNT
                    </a>
                </div>
            </div>
        </aside>

        <!-- PRODUCT GRID -->
        <div class="md:col-span-9">
            <!-- Active Filters -->
            @if(request()->anyFilled(['category', 'size', 'line', 'min_price', 'max_price', 'preset', 'sort', 'search']))
            <div class="mb-6 p-4 bg-black border border-white">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-xs text-gray-400">ACTIVE_FILTERS:</span>
                    @foreach(request()->except(['page']) as $key => $value)
                        @if(!empty($value) && !in_array($key, ['per_page']))
                        <div class="inline-flex items-center px-2 py-1 bg-gray-900 border border-gray-700">
                            <span class="font-mono text-xs">{{ $key }}:</span>
                            <span class="font-mono text-xs font-bold ml-1">{{ $value }}</span>
                            <a href="{{ request()->fullUrlWithoutQuery([$key]) }}" class="ml-2 text-gray-500 hover:text-white">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </div>
                        @endif
                    @endforeach
                    <a href="{{ route('shop.products') }}" class="ml-auto font-mono text-xs text-brand-accent hover:underline">
                        CLEAR_ALL
                    </a>
                </div>
            </div>
            @endif
            
            <!-- Product Count -->
            <div class="flex justify-between items-center mb-6">
                <p class="font-mono text-xs text-gray-400">
                    SHOWING <span class="text-white font-bold">{{ $products->count() }}</span> OF <span class="text-white font-bold">{{ $products->total() }}</span>
                </p>
                <select onchange="window.location.href = '{{ request()->fullUrlWithQuery(['per_page' => '_PER_PAGE_']) }}'.replace('_PER_PAGE_', this.value)" 
                        class="bg-black border border-white text-white font-mono text-xs px-3 py-1 focus:outline-none">
                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 PER PAGE</option>
                    <option value="40" {{ request('per_page', 20) == 40 ? 'selected' : '' }}>40 PER PAGE</option>
                    <option value="60" {{ request('per_page', 20) == 60 ? 'selected' : '' }}>60 PER PAGE</option>
                </select>
            </div>
            
            @if($products->count())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($products as $product)
                <div class="group cursor-pointer relative">
                    <a href="{{ route('product.show', $product->slug) }}" class="block">
                        <div class="border-2 border-white bg-black relative z-10 overflow-hidden">
                            <!-- Badges -->
                            @if($product->has_discount)
                            <div class="absolute top-0 right-0 bg-brand-accent text-white font-bold font-mono text-xs px-2 py-1 z-20">
                                -{{ $product->discount_percentage }}%
                            </div>
                            @endif
                            
                            @if($product->created_at >= now()->subDays(30))
                            <div class="absolute top-0 left-0 bg-white text-black font-bold font-mono text-xs px-2 py-1 z-20">
                                NEW
                            </div>
                            @endif
                            
                            <!-- Image -->
                            <div class="aspect-[4/5] overflow-hidden">
                                <img src="{{ $product->cover_url }}" alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover img-manga">
                            </div>
                            
                            <!-- Info -->
                            <div class="p-3 border-t-2 border-white bg-black">
                                <h3 class="font-display font-bold text-lg uppercase glitch-hover truncate">{{ $product->name }}</h3>
                                <div class="flex justify-between items-end mt-1">
                                    <div>
                                        @if($product->clothingLine)
                                        <p class="font-mono text-gray-400 text-[10px]">{{ $product->clothingLine->name }}</p>
                                        @endif
                                        
                                        <!-- Sizes -->
                                        @if($product->variants->count())
                                        <div class="flex gap-1 mt-1">
                                            @foreach($product->variants->take(3) as $variant)
                                                @if($variant->stock > 0)
                                                <span class="text-[8px] border border-gray-600 px-1 {{ $variant->stock < 3 ? 'text-red-400 border-red-400' : '' }}">
                                                    {{ $variant->size }}
                                                </span>
                                                @endif
                                            @endforeach
                                            @if($product->variants->count() > 3)
                                            <span class="text-[8px] text-gray-500">+{{ $product->variants->count() - 3 }}</span>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Price -->
                                    <div class="text-right">
                                        <span class="font-mono font-bold text-base">
                                            ${{ number_format($product->current_price, 2) }}
                                        </span>
                                        @if($product->has_discount)
                                        <span class="text-[10px] line-through text-gray-500 block">
                                            ${{ number_format($product->price, 2) }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Stock -->
                                <div class="mt-2">
                                    <span class="text-[10px] px-2 py-0.5 {{ $product->stock_quantity > 10 ? 'bg-green-900 text-green-400' : ($product->stock_quantity > 0 ? 'bg-yellow-900 text-yellow-400' : 'bg-red-900 text-red-400') }}">
                                        {{ $product->stock_quantity > 0 ? 'IN_STOCK' : 'OUT_OF_STOCK' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                    <div class="absolute inset-0 bg-brand-accent translate-x-1 translate-y-1 z-0 border-2 border-brand-accent group-hover:translate-x-2 group-hover:translate-y-2 transition-transform"></div>
                    
                    <!-- Quick Add to Cart -->
                    <form action="{{ route('cart.add') }}" method="POST" class="absolute bottom-3 right-3 z-30 opacity-0 group-hover:opacity-100 transition-opacity">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" 
                                class="bg-white text-black border-2 border-white px-3 py-1 font-mono text-xs font-bold uppercase hover:bg-brand-accent hover:text-white transition-colors {{ $product->stock_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                            + CART
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="flex justify-center mt-12 gap-2">
                {{ $products->withQueryString()->onEachSide(1)->links('tenants.designer_hub.pagination.custom-gadyuka') }}
            </div>
            
            @else
            <!-- No Products -->
            <div class="text-center py-16 border-2 border-white bg-black">
                <div class="text-6xl mb-6">🛍️</div>
                <h3 class="text-2xl font-bold mb-2 text-white font-display">NO_ITEMS_FOUND</h3>
                <p class="font-mono text-gray-400 mb-8 max-w-md mx-auto">
                    TRY_CHANGING_FILTER_PARAMETERS
                </p>
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('shop.products') }}" class="bg-white text-black font-display font-bold py-3 px-6 uppercase border-2 border-white hover:bg-black hover:text-white transition-colors">
                        VIEW_ALL
                    </a>
                    <a href="{{ route('home') }}" class="border-2 border-white text-white font-display font-bold py-3 px-6 uppercase hover:bg-white hover:text-black transition-colors">
                        TO_HOME
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Custom Pagination View -->
@if($products->count())
@push('scripts')
<style>
    .pagination {
        display: flex;
        gap: 0.5rem;
    }
    .page-item .page-link {
        width: 2.5rem;
        height: 2.5rem;
        border: 2px solid white;
        background-color: black;
        color: white;
        font-family: 'JetBrains Mono', monospace;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .page-item.active .page-link {
        background-color: white;
        color: black;
    }
    .page-item .page-link:hover {
        background-color: #ff003c;
        color: white;
        border-color: #ff003c;
    }
</style>
@endpush
@endif

@endsection