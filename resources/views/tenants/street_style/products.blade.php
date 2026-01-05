<!-- FILE: resources/views/shop/products.blade.php -->
@extends('tenants.street_style.layouts.artefact')

@section('title', 'ARTEFACT.ROOM // FULL_STORE')

@section('content')
<!-- ========================================================================= -->
<!-- VIEW: SHOP (CATALOG ARCHIVE) -->
<!-- ========================================================================= -->
<div id="view-shop" class="w-full pt-28 pb-20 min-h-screen">
    <div class="max-w-[1400px] mx-auto px-4 md:px-6">
        
        <!-- Page Header -->
        <div class="mb-12 border-b border-gray-800 pb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="bg-neon text-black text-[10px] font-bold px-1 uppercase">Store_ID: #8841</span>
                    <span class="font-tech text-xs text-gray-500">/// FULL_ACCESS</span>
                </div>
                <h1 class="text-5xl md:text-7xl font-syne font-black uppercase text-white italic">
                    Warehouse
                </h1>
            </div>
            <!-- Search Large -->
            <div class="w-full md:w-1/3 relative">
                <form action="{{ route('shop.products') }}" method="GET">
                    <input type="text" name="search" placeholder="Search product name..." 
                           value="{{ request('search') }}"
                           class="w-full bg-transparent border-b-2 border-gray-700 text-white font-marker text-xl p-2 focus:border-neon outline-none placeholder:text-gray-600 placeholder:font-sans">
                    <button type="submit" class="absolute right-2 top-2">
                        <svg class="w-6 h-6 text-gray-500 hover:text-neon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-12">
            
            <!-- SIDEBAR FILTERS -->
            <aside class="w-full lg:w-64 flex-shrink-0 space-y-12">
                <!-- 🔥 ПРЕСЕТЫ (быстрые фильтры) -->
                <div>
                    <h2 class="font-syne font-bold text-white uppercase text-lg mb-3">Быстрые подборки</h2>
                    <div class="space-y-3">
                        <!-- Все товары -->
                        <a href="{{ route('shop.products') }}" 
                           class="block px-4 py-3 border border-gray-700 text-gray-400 hover:text-white hover:border-neon font-tech text-sm transition {{ !request()->has('preset') && !request()->has('sort') ? 'border-neon text-neon' : '' }}">
                            📦 Все товары ({{ $stats['total'] ?? 0 }})
                        </a>
                        
                        <!-- Новинки -->
                        <a href="{{ route('shop.products', ['preset' => 'new']) }}" 
                           class="block px-4 py-3 border border-gray-700 text-gray-400 hover:text-white hover:border-blue-500 font-tech text-sm transition {{ request('preset') == 'new' ? 'border-blue-500 text-blue-500' : '' }}">
                            🆕 Новинки ({{ $stats['new'] ?? 0 }})
                        </a>
                        
                        <!-- Скидки -->
                        <a href="{{ route('shop.products', ['preset' => 'discount']) }}" 
                           class="block px-4 py-3 border border-gray-700 text-gray-400 hover:text-white hover:border-red-500 font-tech text-sm transition {{ request('preset') == 'discount' ? 'border-red-500 text-red-500' : '' }}">
                            🔥 Скидки ({{ $stats['discount'] ?? 0 }})
                        </a>
                    </div>
                </div>

                <form action="{{ route('shop.products') }}" method="GET" id="filterForm">
                    <!-- Категории -->
                    <div>
                        <h3 class="font-syne font-bold text-white uppercase text-lg mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-neon"></span> Category
                        </h3>
                        <div class="space-y-2 font-tech text-sm text-gray-400">
                            @foreach($categories as $category)
                            <label class="flex items-center cursor-pointer hover:text-white group">
                                <input type="checkbox" name="category[]" value="{{ $category->slug }}"
                                       {{ in_array($category->slug, (array)request('category', [])) ? 'checked' : '' }}
                                       class="hidden" onchange="this.form.submit()">
                                <span class="check-box group-hover:border-white"></span>
                                {{ $category->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Размер -->
                    <div>
                        <h3 class="font-syne font-bold text-white uppercase text-lg mb-4">Size_Spec</h3>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach($sizes as $size)
                            <label class="h-10">
                                <input type="checkbox" name="size[]" value="{{ $size }}"
                                       {{ in_array($size, (array)request('size', [])) ? 'checked' : '' }}
                                       class="hidden" onchange="this.form.submit()">
                                <div class="filter-btn h-10 font-tech text-xs font-bold uppercase flex items-center justify-center cursor-pointer {{ in_array($size, (array)request('size', [])) ? 'active' : '' }}">
                                    {{ $size }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Цена -->
                    <div>
                        <h3 class="font-syne font-bold text-white uppercase text-lg mb-4">Price_Limit</h3>
                        <div class="flex items-center justify-between text-gray-400 font-tech text-xs mb-2">
                            <span>0</span>
                            <span>10,000 ₴</span>
                        </div>
                        <div class="mb-4">
                            <input type="range" min="0" max="10000" step="100" 
                                   name="price_range" value="{{ request('price_range', 5000) }}"
                                   class="w-full accent-[#ccff00] bg-gray-700 h-1 appearance-none rounded-lg cursor-pointer"
                                   onchange="updatePriceDisplay(this.value)">
                            <div class="text-center text-neon font-tech text-sm mt-2" id="priceDisplay">
                                {{ request('price_range', 5000) }} ₴
                            </div>
                        </div>
                        <div class="mt-4 flex justify-between">
                            <input type="number" name="min_price" placeholder="Min" 
                                   value="{{ request('min_price', '') }}"
                                   class="w-20 bg-transparent border border-gray-700 text-white font-tech text-xs p-1 text-center"
                                   onchange="this.form.submit()">
                            <input type="number" name="max_price" placeholder="Max" 
                                   value="{{ request('max_price', '') }}"
                                   class="w-20 bg-transparent border border-gray-700 text-white font-tech text-xs p-1 text-center"
                                   onchange="this.form.submit()">
                        </div>
                    </div>

                    <!-- Линейки -->
                    <div>
                        <h3 class="font-syne font-bold text-white uppercase text-lg mb-4">Collection</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($clothingLines as $line)
                            <label>
                                <input type="checkbox" name="line[]" value="{{ $line->slug }}"
                                       {{ in_array($line->slug, (array)request('line', [])) ? 'checked' : '' }}
                                       class="hidden" onchange="this.form.submit()">
                                <div class="border {{ in_array($line->slug, (array)request('line', [])) ? 'border-neon text-neon' : 'border-gray-500 text-gray-500' }} px-2 py-1 font-tech text-xs cursor-pointer hover:border-white hover:text-white transition">
                                    {{ $line->name }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Скрытые поля -->
                    <input type="hidden" name="sort" value="{{ request('sort', 'newest') }}">
                    @if(request('preset'))
                        <input type="hidden" name="preset" value="{{ request('preset') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                </form>
            </aside>

            <!-- ТОВАРЫ -->
            <div class="flex-1">
                <!-- Индикаторы активных фильтров -->
                @if(request()->anyFilled(['category', 'size', 'line', 'min_price', 'max_price', 'preset', 'sort']))
                <div class="mb-8 p-4 bg-gray-900/50 rounded-lg border border-gray-800">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-medium text-gray-400">Активные фильтры:</span>
                        
                        @foreach(request()->except(['page']) as $key => $value)
                            @if(!empty($value) && !is_array($value))
                                @php
                                    $filterNames = [
                                        'category' => 'Категория',
                                        'size' => 'Размер',
                                        'line' => 'Линейка',
                                        'min_price' => 'Цена от',
                                        'max_price' => 'Цена до',
                                        'preset' => 'Пресет',
                                        'sort' => 'Сортировка'
                                    ];
                                    
                                    $displayValue = $value;
                                @endphp
                                <div class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-black border border-gray-700">
                                    <span class="mr-1 text-gray-400">{{ $filterNames[$key] ?? $key }}:</span>
                                    <span class="font-bold text-white">{{ $displayValue }}</span>
                                    <a href="{{ request()->fullUrlWithoutQuery([$key]) }}" class="ml-2 text-gray-500 hover:text-white">
                                        ✕
                                    </a>
                                </div>
                            @endif
                        @endforeach
                        
                        <a href="{{ route('shop.products') }}" class="ml-auto text-sm text-neon hover:text-white font-medium">
                            Очистить все
                        </a>
                    </div>
                </div>
                @endif

                <!-- Сортировка -->
                <div class="flex justify-between items-center mb-6 font-tech text-xs text-gray-500 border-b border-gray-800 pb-4">
                    <span>
                        @if($products->total() > 0)
                            SHOWING {{ $products->firstItem() }}-{{ $products->lastItem() }} OF {{ $products->total() }}
                        @else
                            SHOWING 0 OF 0
                        @endif
                    </span>
                    <div class="flex items-center gap-2">
                        <span>SORT BY:</span>
                        <select name="sort" onchange="document.getElementById('filterForm').submit()" class="bg-transparent text-white border-none outline-none cursor-pointer uppercase font-bold">
                            <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }} class="bg-black">Newest First</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }} class="bg-black">Price: Low to High</option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }} class="bg-black">Price: High to Low</option>
                            <option value="discount" {{ request('sort') == 'discount' ? 'selected' : '' }} class="bg-black">Biggest Discount</option>
                        </select>
                    </div>
                </div>

                @if($products->count())
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 gap-y-12">
                    @foreach($products as $product)
                    <div class="poster-card p-2 group cursor-pointer" onclick="window.location='{{ route('product.show', $product->slug) }}'">
                        <!-- Лента -->
                        @if($loop->index % 3 == 0)
                            <div class="tape-strip -top-2 left-1/2 -translate-x-1/2 w-20 h-6 bg-white/40 rotate-1"></div>
                        @elseif($loop->index % 3 == 1)
                            <div class="tape-strip -top-2 right-4 w-16 h-6 bg-white/40 -rotate-2"></div>
                        @else
                            <div class="tape-strip -top-2 left-4 w-16 h-6 bg-white/40 rotate-3"></div>
                        @endif
                        
                        <!-- Бейдж HOT -->
                        @if($product->has_discount && $product->discount_percentage >= 30)
                            <div class="absolute top-2 right-2 z-10 font-marker text-pink-500 text-sm rotate-12 bg-white px-1 border border-black shadow-sm">HOT</div>
                        @endif
                        
                        <!-- Бейдж NEW -->
                        @if($product->created_at >= now()->subDays(30))
                            <div class="absolute top-2 left-2 bg-neon text-black text-[10px] font-bold px-1">NEW ARRIVAL</div>
                        @endif
                        
                        <!-- Изображение -->
                        <div class="relative overflow-hidden mb-2 border border-black h-64 bg-gray-200">
                            <img src="{{ $product->cover_url }}" alt="{{ $product->name }}" 
                                 class="w-full h-full object-cover filter grayscale contrast-125 group-hover:grayscale-0 transition duration-500">
                            
                            @if($product->stock_quantity <= 0)
                                <div class="absolute inset-0 bg-black/70 flex items-center justify-center">
                                    <span class="font-marker text-2xl rotate-[-15deg] text-white">SOLD OUT</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Информация -->
                        <div class="p-2">
                            <h3 class="font-syne font-bold text-xl uppercase leading-none">{{ $product->name }}</h3>
                            <div class="flex justify-between items-end mt-2">
                                <span class="font-tech text-xs text-gray-500">
                                    @if($product->clothingLine)
                                        {{ strtoupper($product->clothingLine->name) }}
                                    @else
                                        {{ strtoupper($product->category->name ?? 'GENERAL') }}
                                    @endif
                                </span>
                                <div class="text-right">
                                    @if($product->has_discount)
                                        <span class="font-marker text-lg text-neon">₴{{ number_format($product->current_price, 0) }}</span>
                                        <span class="font-tech text-xs text-gray-500 line-through block">₴{{ number_format($product->price, 0) }}</span>
                                    @else
                                        <span class="font-marker text-lg">₴{{ number_format($product->price, 0) }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Размеры -->
                            @if($product->variants->count())
                            <div class="mt-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($product->variants->take(5) as $variant)
                                        @if($variant->stock > 0)
                                        <span class="text-xs px-2 py-1 border border-gray-300 rounded {{ $variant->stock < 3 ? 'bg-red-50 text-red-700 border-red-300' : '' }}">
                                            {{ $variant->size }}
                                            @if($variant->stock < 3)
                                            <span class="text-xs">({{ $variant->stock }})</span>
                                            @endif
                                        </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            <!-- Кнопка добавления -->
                            <form action="{{ route('cart.add') }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                
                                <button type="submit" 
                                        class="w-full py-2 bg-black text-white font-tech text-sm uppercase hover:bg-neon hover:text-black transition flex items-center justify-center {{ $product->stock_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                                    {{ $product->stock_quantity > 0 ? 'В корзину' : 'Нет в наличии' }}
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Пагинация -->
                <div class="mt-16 border-t border-gray-800 pt-8 flex justify-between items-center font-tech text-xs text-gray-500">
                    @if($products->onFirstPage())
                        <span class="opacity-50"><- PREV</span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" class="hover:text-white hover:underline"><- PREV</a>
                    @endif
                    
                    <div class="flex gap-4">
                        @php
                            $current = $products->currentPage();
                            $last = $products->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp
                        
                        @if($start > 1)
                            <a href="{{ $products->url(1) }}" class="hover:text-white cursor-pointer">1</a>
                            @if($start > 2)<span>...</span>@endif
                        @endif
                        
                        @for($page = $start; $page <= $end; $page++)
                            @if($page == $current)
                                <span class="text-neon border-b border-neon">{{ $page }}</span>
                            @else
                                <a href="{{ $products->url($page) }}" class="hover:text-white cursor-pointer">{{ $page }}</a>
                            @endif
                        @endfor
                        
                        @if($end < $last)
                            @if($end < $last - 1)<span>...</span>@endif
                            <a href="{{ $products->url($last) }}" class="hover:text-white cursor-pointer">{{ $last }}</a>
                        @endif
                    </div>
                    
                    @if($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="hover:text-white hover:underline">NEXT -></a>
                    @else
                        <span class="opacity-50">NEXT -></span>
                    @endif
                </div>
                @else
                <!-- Нет товаров -->
                <div class="text-center py-16 bg-gray-900/50 rounded-lg border border-gray-800">
                    <div class="text-6xl mb-6">🛍️</div>
                    <h3 class="text-2xl font-syne font-bold mb-2 text-white">NO PRODUCTS FOUND</h3>
                    <p class="font-tech text-gray-400 mb-8 max-w-md mx-auto">
                        Попробуйте изменить параметры фильтрации или выберите другую категорию
                    </p>
                    <div class="flex justify-center space-x-4">
                        <a href="{{ route('shop.products') }}" 
                           class="inline-block bg-neon text-black font-tech text-sm uppercase px-6 py-3 hover:bg-white transition">
                            Смотреть все товары
                        </a>
                        <a href="{{ url('/') }}" 
                           class="inline-block border border-gray-700 text-white font-tech text-sm uppercase px-6 py-3 hover:border-neon hover:text-neon transition">
                            На главную
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function updatePriceDisplay(value) {
        document.getElementById('priceDisplay').textContent = value + ' ₴';
    }
    
    // Инициализация
    document.addEventListener('DOMContentLoaded', function() {
        const priceRange = document.querySelector('input[name="price_range"]');
        if (priceRange) {
            updatePriceDisplay(priceRange.value);
            
            // Отправка формы при отпускании ползунка
            priceRange.addEventListener('mouseup', function() {
                this.form.submit();
            });
        }
        
        // Инициализация чекбоксов
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            if (checkbox.checked) {
                const checkBox = checkbox.nextElementSibling?.querySelector('.check-box');
                if (checkBox) {
                    checkBox.style.background = 'var(--c-neon)';
                    checkBox.style.borderColor = 'var(--c-neon)';
                }
            }
        });
    });
</script>
@endsection