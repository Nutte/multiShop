<!-- FILE: resources/views/shop/products.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Заголовок и статистика -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-4xl font-black uppercase tracking-tighter mb-2 theme-text">Все товары</h1>
                <p class="theme-muted">
                    <span class="font-bold">{{ $stats['total'] }}</span> товаров в каталоге
                    • <span class="text-green-600 font-bold">{{ $stats['new'] }}</span> новинок
                    • <span class="text-red-600 font-bold">{{ $stats['discount'] }}</span> со скидкой
                </p>
            </div>
            
            <!-- Быстрые действия -->
            <div class="mt-4 md:mt-0 flex space-x-2">
                <a href="{{ route('home') }}" class="px-4 py-2 border theme-border rounded font-bold hover:bg-gray-50 transition">
                    ← На главную
                </a>
                @if(request()->anyFilled(['category', 'size', 'line', 'min_price', 'max_price', 'preset']))
                <a href="{{ route('shop.products') }}" class="px-4 py-2 bg-gray-800 text-white rounded font-bold hover:bg-gray-900 transition">
                    ✕ Сбросить фильтры
                </a>
                @endif
            </div>
        </div>
        
        <!-- 🔥 ПРЕСЕТЫ (быстрые фильтры) -->
        <div class="mb-8">
            <h2 class="text-lg font-bold mb-3 theme-text">Быстрые подборки</h2>
            <div class="flex flex-wrap gap-3">
                <!-- Все товары -->
                <a href="{{ route('shop.products') }}" 
                   class="px-5 py-2.5 border theme-border rounded-lg font-bold transition flex items-center {{ !request()->has('preset') && !request()->has('sort') ? 'theme-btn shadow-lg' : 'hover:bg-gray-50' }}">
                    <span class="mr-2">📦</span>
                    Все товары
                    <span class="ml-2 text-sm opacity-75">({{ $stats['total'] }})</span>
                </a>
                
                <!-- Новинки -->
                <a href="{{ route('shop.products', ['preset' => 'new']) }}" 
                   class="px-5 py-2.5 border theme-border rounded-lg font-bold transition flex items-center {{ request('preset') == 'new' ? 'bg-blue-50 border-blue-300 text-blue-700 shadow-lg' : 'hover:bg-gray-50' }}">
                    <span class="mr-2 text-blue-600">🆕</span>
                    Новинки
                    <span class="ml-2 text-sm opacity-75">({{ $stats['new'] }})</span>
                </a>
                
                <!-- Скидки -->
                <a href="{{ route('shop.products', ['preset' => 'discount']) }}" 
                   class="px-5 py-2.5 border theme-border rounded-lg font-bold transition flex items-center {{ request('preset') == 'discount' ? 'bg-red-50 border-red-300 text-red-700 shadow-lg' : 'hover:bg-gray-50' }}">
                    <span class="mr-2 text-red-600">🔥</span>
                    Скидки
                    <span class="ml-2 text-sm opacity-75">({{ $stats['discount'] }})</span>
                </a>
                
                <!-- По размеру скидки -->
                <a href="{{ route('shop.products', ['sort' => 'discount']) }}" 
                   class="px-5 py-2.5 border theme-border rounded-lg font-bold transition flex items-center {{ request('sort') == 'discount' ? 'bg-orange-50 border-orange-300 text-orange-700 shadow-lg' : 'hover:bg-gray-50' }}">
                    <span class="mr-2 text-orange-600">⬇️</span>
                    По размеру скидки
                </a>
            </div>
        </div>
        
        <!-- Детальные фильтры -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold theme-text">Расширенные фильтры</h3>
                <button type="button" onclick="toggleFilters()" class="md:hidden px-3 py-1 border rounded text-sm">
                    <span id="filterToggle">Показать фильтры</span>
                </button>
            </div>
            
            <form method="GET" action="{{ route('shop.products') }}" id="filterForm" class="hidden md:block">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    
                    <!-- Категория -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Категория</label>
                        <select name="category" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Все категории</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->slug }}" {{ request('category') == $category->slug ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Размер -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Размер</label>
                        <select name="size" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Все размеры</option>
                            @foreach($sizes as $size)
                                <option value="{{ $size }}" {{ request('size') == $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Линейка -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Линейка</label>
                        <select name="line" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Все линейки</option>
                            @foreach($clothingLines as $line)
                                <option value="{{ $line->slug }}" {{ request('line') == $line->slug ? 'selected' : '' }}>
                                    {{ $line->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Цена -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Цена ($)</label>
                        <div class="flex space-x-2">
                            <input type="number" name="min_price" placeholder="От" value="{{ request('min_price') }}" 
                                   class="w-1/2 border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" min="0" step="0.01">
                            <input type="number" name="max_price" placeholder="До" value="{{ request('max_price') }}" 
                                   class="w-1/2 border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" min="0" step="0.01">
                        </div>
                    </div>
                    
                    <!-- Сортировка -->
                    <div>
                        <label class="block text-sm font-medium mb-2">Сортировка</label>
                        <select name="sort" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Новинки</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>По возрастанию цены</option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>По убыванию цены</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>По названию (А-Я)</option>
                            <option value="discount" {{ request('sort') == 'discount' ? 'selected' : '' }}>По размеру скидки</option>
                        </select>
                    </div>
                    
                    <!-- Скрытые поля для пресетов -->
                    @if(request('preset'))
                        <input type="hidden" name="preset" value="{{ request('preset') }}">
                    @endif
                    
                    <!-- Кнопки -->
                    <div class="flex space-x-2 items-end md:col-span-2">
                        <button type="submit" class="theme-btn px-6 py-2.5 rounded font-bold hover:opacity-90 transition flex-1">
                            <span class="mr-2">🔍</span>
                            Применить фильтры
                        </button>
                        <a href="{{ route('shop.products') }}" class="px-6 py-2.5 border theme-border rounded font-bold hover:bg-gray-50 transition flex items-center justify-center">
                            <span class="mr-2">↺</span>
                            Сбросить
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Индикаторы активных фильтров -->
        @if(request()->anyFilled(['category', 'size', 'line', 'min_price', 'max_price', 'preset', 'sort']))
        <div class="mb-8 p-4 bg-gray-50 rounded-lg">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium theme-muted">Активные фильтры:</span>
                
                @foreach(request()->except(['page']) as $key => $value)
                    @if(!empty($value))
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
                            
                            if ($key === 'category') {
                                $displayValue = $categories->where('slug', $value)->first()->name ?? $value;
                            } elseif ($key === 'line') {
                                $displayValue = $clothingLines->where('slug', $value)->first()->name ?? $value;
                            } elseif (in_array($key, ['min_price', 'max_price'])) {
                                $displayValue = '$' . $value;
                            } elseif ($key === 'preset') {
                                $presetNames = [
                                    'new' => 'Новинки',
                                    'discount' => 'Скидки'
                                ];
                                $displayValue = $presetNames[$value] ?? $value;
                            } elseif ($key === 'sort') {
                                $sortNames = [
                                    'newest' => 'Новинки',
                                    'price_asc' => 'По возрастанию цены',
                                    'price_desc' => 'По убыванию цены',
                                    'name' => 'По названию',
                                    'discount' => 'По размеру скидки'
                                ];
                                $displayValue = $sortNames[$value] ?? $value;
                            }
                        @endphp
                        <div class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-white border shadow-sm">
                            <span class="mr-1">{{ $filterNames[$key] ?? $key }}:</span>
                            <span class="font-bold">{{ $displayValue }}</span>
                            <a href="{{ request()->fullUrlWithoutQuery([$key]) }}" class="ml-2 text-gray-500 hover:text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        </div>
                    @endif
                @endforeach
                
                <a href="{{ route('shop.products') }}" class="ml-auto text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Очистить все
                </a>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Сетка товаров -->
    @if($products->count())
    <div class="mb-4 flex justify-between items-center">
        <p class="theme-muted">
            Показано <span class="font-bold">{{ $products->count() }}</span> из <span class="font-bold">{{ $products->total() }}</span> товаров
        </p>
        <div class="flex items-center space-x-2">
            <span class="text-sm theme-muted">На странице:</span>
            <select onchange="window.location.href = '{{ request()->fullUrlWithQuery(['per_page' => '_PER_PAGE_']) }}'.replace('_PER_PAGE_', this.value)" class="border rounded px-2 py-1 text-sm">
                <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                <option value="40" {{ request('per_page', 20) == 40 ? 'selected' : '' }}>40</option>
                <option value="60" {{ request('per_page', 20) == 60 ? 'selected' : '' }}>60</option>
            </select>
        </div>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        @foreach($products as $product)
        <div class="group relative block bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden">
            <a href="{{ route('product.show', $product->slug) }}" class="block h-full">
                <!-- Изображение -->
                <div class="relative overflow-hidden aspect-[3/4] bg-gray-100">
                    <img src="{{ $product->cover_url }}" alt="{{ $product->name }}" 
                         class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                    
                    <!-- Бейджи -->
                    <div class="absolute top-2 right-2 flex flex-col space-y-1">
                        @if($product->has_discount)
                        <div class="bg-red-600 text-white px-2 py-1 text-xs font-bold rounded shadow-lg">
                            -{{ $product->discount_percentage }}%
                        </div>
                        @endif
                        
                        @if($product->created_at >= now()->subDays(30))
                        <div class="bg-blue-600 text-white px-2 py-1 text-xs font-bold rounded shadow-lg">
                            NEW
                        </div>
                        @endif
                    </div>
                    
                    <!-- Быстрый просмотр (ховер) -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <span class="bg-white text-black px-4 py-2 rounded-full text-sm font-bold">
                            Быстрый просмотр
                        </span>
                    </div>
                </div>
                
                <!-- Информация -->
                <div class="p-4">
                    <div class="mb-2">
                        <h3 class="font-bold text-gray-900 truncate group-hover:text-blue-600 transition">{{ $product->name }}</h3>
                        @if($product->clothingLine)
                        <p class="text-xs text-gray-500 mt-1">{{ $product->clothingLine->name }}</p>
                        @endif
                    </div>
                    
                    <!-- Цена -->
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="text-xl font-bold text-gray-900">
                                ${{ number_format($product->current_price, 2) }}
                            </span>
                            @if($product->has_discount)
                            <span class="ml-2 text-sm text-gray-500 line-through">
                                ${{ number_format($product->price, 2) }}
                            </span>
                            @endif
                        </div>
                        
                        <!-- Наличие -->
                        <span class="text-xs px-2 py-1 rounded-full {{ $product->stock_quantity > 10 ? 'bg-green-100 text-green-800' : ($product->stock_quantity > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $product->stock_quantity > 0 ? 'В наличии' : 'Нет в наличии' }}
                        </span>
                    </div>
                    
                    <!-- Размеры -->
                    @if($product->variants->count())
                    <div class="mb-3">
                        <p class="text-xs text-gray-500 mb-1">Доступные размеры:</p>
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
                            
                            @if($product->variants->count() > 5)
                            <span class="text-xs text-gray-500 self-center">+{{ $product->variants->count() - 5 }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <!-- Категории -->
                    <div class="flex flex-wrap gap-1">
                        @foreach($product->categories->take(2) as $category)
                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded">
                            {{ $category->name }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </a>
            
            <!-- Кнопка быстрого добавления в корзину -->
            <div class="p-4 pt-0">
                <form action="{{ route('cart.add') }}" method="POST" class="mt-2">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    
                    <button type="submit" 
                            class="w-full py-2.5 bg-gray-900 text-white rounded font-bold hover:bg-black transition flex items-center justify-center {{ $product->stock_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}>
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        {{ $product->stock_quantity > 0 ? 'В корзину' : 'Нет в наличии' }}
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Пагинация -->
    <div class="mt-12 flex justify-center">
        {{ $products->withQueryString()->links() }}
    </div>
    
    @else
    <!-- Нет товаров -->
    <div class="text-center py-16 bg-gray-50 rounded-lg">
        <div class="text-6xl mb-6">🛍️</div>
        <h3 class="text-2xl font-bold mb-2 theme-text">Товары не найдены</h3>
        <p class="theme-muted mb-8 max-w-md mx-auto">
            Попробуйте изменить параметры фильтрации или выберите другую категорию
        </p>
        <div class="flex justify-center space-x-4">
            <a href="{{ route('shop.products') }}" class="theme-btn px-6 py-3 rounded font-bold">
                Смотреть все товары
            </a>
            <a href="{{ route('home') }}" class="px-6 py-3 border theme-border rounded font-bold hover:bg-gray-50">
                На главную
            </a>
        </div>
    </div>
    @endif
</div>

<!-- JavaScript для фильтров (мобильная версия) -->
<script>
    function toggleFilters() {
        const form = document.getElementById('filterForm');
        const toggle = document.getElementById('filterToggle');
        
        if (form.classList.contains('hidden')) {
            form.classList.remove('hidden');
            toggle.textContent = 'Скрыть фильтры';
        } else {
            form.classList.add('hidden');
            toggle.textContent = 'Показать фильтры';
        }
    }
    
    // Авто-применение некоторых фильтров
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('#filterForm select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                if (this.value) {
                    this.form.submit();
                }
            });
        });
    });
</script>

<style>
    .theme-btn {
        background-color: var(--color-primary, #3b82f6);
        color: white;
        border-color: var(--color-primary, #3b82f6);
    }
    
    .theme-btn:hover {
        background-color: var(--color-primary-dark, #2563eb);
    }
    
    .theme-border {
        border-color: var(--color-border, #e5e7eb);
    }
    
    .theme-text {
        color: var(--color-text, #1f2937);
    }
    
    .theme-muted {
        color: var(--color-muted, #6b7280);
    }
    
    .theme-card {
        background: var(--color-card, #ffffff);
        border-radius: 0.5rem;
        overflow: hidden;
    }
</style>
@endsection