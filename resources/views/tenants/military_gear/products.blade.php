@extends('tenants.military_gear.layouts.military')

@section('title', 'Арсенал | KARAKURT')

@section('content')
<!-- Catalog Header -->
<div class="border-b border-military-gray bg-military-black/50 py-12">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-6xl font-bold uppercase text-white mb-2">Armory</h1>
        <p class="text-military-text font-mono text-xs tracking-widest">/// FULL PRODUCT LISTING</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Quick Filters / Presets -->
    <div class="mb-8">
        <h2 class="text-lg font-bold mb-3 text-white">/// QUICK SELECT</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('shop.products') }}" 
               class="px-5 py-2.5 border border-military-gray text-white font-mono uppercase text-xs hover:border-military-accent hover:text-military-accent transition-colors {{ !request()->has('preset') && !request()->has('sort') ? 'bg-military-gray' : '' }}">
                ALL ITEMS ({{ $stats['total'] ?? 0 }})
            </a>
            <a href="{{ route('shop.products', ['preset' => 'new']) }}" 
               class="px-5 py-2.5 border border-military-gray text-white font-mono uppercase text-xs hover:border-military-accent hover:text-military-accent transition-colors {{ request('preset') == 'new' ? 'border-military-accent text-military-accent' : '' }}">
                NEW DROPS ({{ $stats['new'] ?? 0 }})
            </a>
            <a href="{{ route('shop.products', ['preset' => 'discount']) }}" 
               class="px-5 py-2.5 border border-military-gray text-white font-mono uppercase text-xs hover:border-military-accent hover:text-military-accent transition-colors {{ request('preset') == 'discount' ? 'border-military-accent text-military-accent' : '' }}">
                DISCOUNT ({{ $stats['discount'] ?? 0 }})
            </a>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- SIDEBAR FILTERS -->
        <aside class="w-full lg:w-72 shrink-0">
            <!-- Search -->
            <div class="relative mb-6">
                <form action="{{ route('shop.products') }}" method="GET" class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="SEARCH ITEM [REF]" 
                           class="w-full bg-black border border-military-gray text-white px-4 py-3 pl-10 focus:border-military-accent focus:outline-none font-mono text-sm">
                    <svg class="w-4 h-4 text-military-text absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </form>
            </div>

            <!-- Mobile Filter Toggle -->
            <button class="lg:hidden w-full border border-military-gray py-3 text-white font-mono uppercase text-xs flex justify-between px-4 items-center bg-military-dark mb-4" 
                    onclick="document.getElementById('mobile-filters').classList.toggle('hidden')">
                <span>Filter Parameters</span>
                <span class="text-military-accent">+</span>
            </button>

            <!-- Filters Container -->
            <form method="GET" action="{{ route('shop.products') }}" 
                  id="mobile-filters" 
                  class="hidden lg:block space-y-6 p-4 lg:p-0 border lg:border-none border-military-gray bg-military-dark lg:bg-transparent">
                
                <!-- Categories -->
                <div>
                    <h3 class="text-white font-bold uppercase mb-4 text-sm flex items-center gap-2">
                        <span class="w-1 h-1 bg-military-accent"></span> Categories
                    </h3>
                    <div class="space-y-2">
                        @foreach($categories as $category)
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="category" value="{{ $category->slug }}" 
                                   class="appearance-none w-4 h-4 border border-military-text bg-transparent tactical-checkbox transition-colors"
                                   {{ request('category') == $category->slug ? 'checked' : '' }}
                                   onchange="this.form.submit()">
                            <span class="text-military-text font-mono text-xs uppercase group-hover:text-white transition-colors">
                                {{ $category->name }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Size -->
                <div class="border-t border-military-gray/30 pt-6">
                    <h3 class="text-white font-bold uppercase mb-4 text-sm flex items-center gap-2">
                        <span class="w-1 h-1 bg-military-accent"></span> Size
                    </h3>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['S', 'M', 'L', 'XL', 'XXL'] as $size)
                        <button type="button" 
                                onclick="window.location='{{ request()->fullUrlWithQuery(['size' => $size]) }}'"
                                class="h-8 border {{ request('size') == $size ? 'border-white bg-white text-black' : 'border-military-text text-military-text' }} font-mono text-xs hover:border-white hover:text-white transition-colors">
                            {{ $size }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Price Range -->
                <div class="border-t border-military-gray/30 pt-6">
                    <h3 class="text-white font-bold uppercase mb-4 text-sm flex items-center gap-2">
                        <span class="w-1 h-1 bg-military-accent"></span> Price Range (₴)
                    </h3>
                    <div class="flex items-center gap-2 mb-4">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="MIN" 
                               class="w-1/2 bg-black border border-military-gray text-white px-2 py-2 font-mono text-xs focus:border-military-accent focus:outline-none">
                        <span class="text-military-gray">-</span>
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="MAX" 
                               class="w-1/2 bg-black border border-military-gray text-white px-2 py-2 font-mono text-xs focus:border-military-accent focus:outline-none">
                    </div>
                    <button type="submit" class="w-full border border-military-text text-military-text hover:text-white hover:border-white text-xs font-mono uppercase py-2 transition-colors">
                        Update Filter
                    </button>
                </div>
                
                <!-- Clear Filters -->
                @if(request()->anyFilled(['category', 'size', 'min_price', 'max_price', 'preset', 'search']))
                <div class="border-t border-military-gray/30 pt-6">
                    <a href="{{ route('shop.products') }}" 
                       class="w-full border border-red-900 text-red-500 hover:bg-red-900/20 text-xs font-mono uppercase py-2 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        CLEAR ALL FILTERS
                    </a>
                </div>
                @endif
            </form>
        </aside>

        <!-- PRODUCT GRID -->
        <div class="flex-grow">
            <!-- Sort / Count -->
            <div class="flex justify-between items-center mb-6 pb-2 border-b border-military-gray/30">
                <p class="font-mono text-xs text-military-text">
                    SHOWING {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} OF {{ $products->total() }} RESULTS
                </p>
                <select onchange="window.location.href = '{{ request()->fullUrlWithQuery(['sort' => '_SORT_']) }}'.replace('_SORT_', this.value)" 
                        class="bg-transparent text-military-text font-mono text-xs uppercase focus:outline-none cursor-pointer hover:text-white">
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Sort by: Newest</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Sort by: Price (Low > High)</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Sort by: Price (High > Low)</option>
                    <option value="discount" {{ request('sort') == 'discount' ? 'selected' : '' }}>Sort by: Discount</option>
                </select>
            </div>

            <!-- Active Filters -->
            @if(request()->anyFilled(['category', 'size', 'min_price', 'max_price', 'preset', 'search']))
            <div class="mb-6 p-4 bg-military-dark/50 border border-military-gray">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-mono text-military-text">ACTIVE FILTERS:</span>
                    
                    @foreach(request()->except(['page']) as $key => $value)
                        @if(!empty($value))
                            @php
                                $filterNames = [
                                    'category' => 'Category',
                                    'size' => 'Size',
                                    'min_price' => 'Min Price',
                                    'max_price' => 'Max Price',
                                    'preset' => 'Preset',
                                    'search' => 'Search'
                                ];
                                
                                $displayValue = $value;
                                
                                if ($key === 'category') {
                                    $displayValue = $categories->where('slug', $value)->first()->name ?? $value;
                                } elseif (in_array($key, ['min_price', 'max_price'])) {
                                    $displayValue = $value . ' ₴';
                                } elseif ($key === 'preset') {
                                    $presetNames = [
                                        'new' => 'New Drops',
                                        'discount' => 'Discount'
                                    ];
                                    $displayValue = $presetNames[$value] ?? $value;
                                }
                            @endphp
                            <div class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-mono bg-military-black border border-military-gray">
                                <span class="mr-1 text-military-text">{{ $filterNames[$key] ?? $key }}:</span>
                                <span class="text-white">{{ $displayValue }}</span>
                                <a href="{{ request()->fullUrlWithoutQuery([$key]) }}" class="ml-2 text-military-text hover:text-white">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Grid -->
            @if($products->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($products as $product)
                <div class="group relative tech-border bg-military-dark p-2 transition-transform duration-300 hover:-translate-y-1">
                    <div class="corner-accent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <a href="{{ route('product.show', $product->slug) }}" class="block">
                        <div class="relative aspect-[3/4] overflow-hidden bg-military-black mb-4">
                            <img src="{{ $product->cover_url }}" alt="{{ $product->name }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 opacity-90 group-hover:opacity-100">
                            
                            <!-- Badges -->
                            <div class="absolute top-2 left-2 flex flex-col gap-1">
                                @if($product->created_at >= now()->subDays(30))
                                <div class="bg-black/80 backdrop-blur text-white text-[10px] font-mono px-2 py-1 border border-military-gray">
                                    NEW DROP
                                </div>
                                @endif
                                @if($product->has_discount)
                                <div class="bg-military-accent text-black text-[10px] font-mono px-2 py-1 font-bold">
                                    -{{ $product->discount_percentage }}%
                                </div>
                                @endif
                            </div>
                            
                            <!-- Stock Status -->
                            <div class="absolute bottom-2 right-2">
                                <span class="text-xs font-mono px-2 py-1 {{ $product->stock_quantity > 10 ? 'bg-green-900/50 text-green-400' : ($product->stock_quantity > 0 ? 'bg-yellow-900/50 text-yellow-400' : 'bg-red-900/50 text-red-400') }}">
                                    {{ $product->stock_quantity > 0 ? 'IN STOCK' : 'OUT OF STOCK' }}
                                </span>
                            </div>
                        </div>
                    </a>
                    
                    <div class="p-2">
                        <a href="{{ route('product.show', $product->slug) }}">
                            <h3 class="text-white font-bold uppercase tracking-wide group-hover:text-military-accent transition-colors mb-1">
                                {{ $product->name }}
                            </h3>
                        </a>
                        <p class="text-military-text text-xs font-mono mb-3">{{ $product->short_description ?? '' }}</p>
                        
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-white font-mono font-bold">{{ number_format($product->current_price, 0) }} ₴</span>
                                @if($product->has_discount)
                                <span class="text-military-text text-xs line-through decoration-military-accent ml-2">
                                    {{ number_format($product->price, 0) }} ₴
                                </span>
                                @endif
                            </div>
                            
                            <!-- Quick add to cart -->
                            @if($product->stock_quantity > 0)
                            <form action="{{ route('cart.add') }}" method="POST" onclick="event.stopPropagation()">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" 
                                        class="px-3 py-1.5 bg-military-accent text-black font-mono uppercase text-xs hover:bg-orange-500 transition-colors">
                                    + ADD
                                </button>
                            </form>
                            @else
                            <span class="px-3 py-1.5 border border-military-gray text-military-text font-mono uppercase text-xs">
                                SOLD OUT
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12 flex justify-center">
                <div class="flex items-center gap-2 font-mono text-sm">
                    {{-- Previous Page --}}
                    @if($products->onFirstPage())
                    <span class="w-8 h-8 flex items-center justify-center border border-military-gray text-military-text cursor-not-allowed">
                        &lsaquo;
                    </span>
                    @else
                    <a href="{{ $products->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center border border-military-gray text-military-text hover:text-white hover:border-white transition-colors">
                        &lsaquo;
                    </a>
                    @endif

                    {{-- Page Numbers --}}
                    @for($i = 1; $i <= $products->lastPage(); $i++)
                        @if($i == $products->currentPage())
                        <span class="w-8 h-8 flex items-center justify-center bg-military-accent text-black font-bold">
                            {{ $i }}
                        </span>
                        @else
                        <a href="{{ $products->url($i) }}" class="w-8 h-8 flex items-center justify-center border border-military-gray text-military-text hover:text-white hover:border-white transition-colors">
                            {{ $i }}
                        </a>
                        @endif
                    @endfor

                    {{-- Next Page --}}
                    @if($products->hasMorePages())
                    <a href="{{ $products->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center border border-military-gray text-military-text hover:text-white hover:border-white transition-colors">
                        &rsaquo;
                    </a>
                    @else
                    <span class="w-8 h-8 flex items-center justify-center border border-military-gray text-military-text cursor-not-allowed">
                        &rsaquo;
                    </span>
                    @endif
                </div>
            </div>
            @else
            <!-- No products -->
            <div class="text-center py-16">
                <div class="text-6xl mb-6 text-military-gray">🛡️</div>
                <h3 class="text-2xl font-bold mb-2 text-white">NO ITEMS FOUND</h3>
                <p class="text-military-text font-mono mb-8 max-w-md mx-auto">
                    // MISSION PARAMETERS YIELDED ZERO RESULTS<br>
                    ADJUST FILTERS AND TRY AGAIN
                </p>
                <a href="{{ route('shop.products') }}" 
                   class="px-8 py-4 bg-military-accent text-black font-bold uppercase tracking-widest hover:bg-white transition-colors">
                    RESET FILTERS
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection