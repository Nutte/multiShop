<!-- FILE: resources/views/admin/products/form.blade.php -->
@extends('layouts.admin')

@php
    $user = auth()->user();
    $isSuperAdmin = $user->role === 'super_admin';
    $isEdit = isset($product) && $product->exists;
    $title = $isEdit ? 'Edit Product' : 'Create Product';
    $storeName = $currentTenantId ? config("tenants.tenants.{$currentTenantId}.name") : null;
    
    // Ключевая проверка: получаем tenant_id из URL или запроса
    $urlTenantId = request()->get('tenant_id');
    $hasTenantInUrl = !empty($urlTenantId);
    
    // Определяем, нужно ли показывать выбор магазина
    $shouldShowStoreSelection = $isSuperAdmin && !$isEdit && !$hasTenantInUrl;
@endphp

@section('title', $title)

@section('content')

{{-- ИСПОЛЬЗУЕМ IF-ELSE ВМЕСТО RETURN --}}
@if($shouldShowStoreSelection)
    <div class="max-w-2xl mx-auto mt-10">
        <div class="bg-white p-8 rounded shadow-lg border-t-4 border-blue-600 text-center">
            <h1 class="text-2xl font-bold mb-4">Create New Product</h1>
            <p class="text-gray-500 mb-6">Select a store to load categories and sizes.</p>
            
            <div class="inline-block w-full max-w-md text-left">
                <select onchange="if(this.value) window.location.href = '{{ route('admin.products.create') }}?tenant_id=' + this.value"
                        class="w-full border p-3 rounded bg-yellow-50 border-yellow-300 font-bold text-gray-800">
                    <option value="">-- Choose Store --</option>
                    @foreach(config('tenants.tenants') as $id => $data)
                        <option value="{{ $id }}" {{ $urlTenantId == $id ? 'selected' : '' }}>🏪 {{ $data['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-6">
                <a href="{{ route('admin.products.index') }}" class="text-gray-500 hover:underline">
                    ← Back to Products
                </a>
            </div>
        </div>
    </div>
@else
    {{-- ОСНОВНАЯ ФОРМА ТЕПЕРЬ В БЛОКЕ ELSE --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold">{{ $title }}</h1>
            <!-- Для супер-админа при создании показываем выбранный магазин -->
            @if($isSuperAdmin && !$isEdit)
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm font-bold border border-blue-200">
                    Store: {{ $storeName ?? 'Unknown' }}
                    <a href="{{ route('admin.products.create') }}" class="ml-2 text-xs text-blue-600 hover:underline">(Change)</a>
                </span>
            @endif
        </div>
        @if($isEdit && $previewUrl)
            <a href="{{ $previewUrl }}" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-500 flex items-center gap-2 font-bold shadow">
                <span>👁️</span> Preview on Site
            </a>
        @endif
    </div>

    <div class="max-w-6xl mx-auto bg-white rounded shadow p-6" 
         x-data="{
            variants: {{ json_encode($product->variants->map(fn($v) => ['size' => $v->size, 'stock' => $v->stock])->values()->all()) }},
            manualStock: {{ $product->stock_quantity ?? 0 }},
            get hasVariants() { return this.variants.length > 0; },
            get calculatedStock() { return this.variants.reduce((sum, v) => sum + (parseInt(v.stock) || 0), 0); },
            get currentStock() { return this.hasVariants ? this.calculatedStock : this.manualStock; },
            addVariant() { this.variants.push({size: '', stock: 0}); },
            removeVariant(index) { this.variants.splice(index, 1); }
         }">
        
        <form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf
            @if($method !== 'POST') @method($method) @endif
            
            <!-- Для супер-админа при создании добавляем hidden поле -->
            @if($isSuperAdmin && !$isEdit)
                <input type="hidden" name="target_tenant" value="{{ $currentTenantId }}">
            @endif
            
            <!-- Также добавляем tenant_id для консистентности -->
            @if(isset($currentTenantId))
                <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- ЛЕВАЯ КОЛОНКА -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold mb-2">Name</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full border p-2 rounded" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full border p-2 rounded" required>
                        </div>
                    </div>
                    
                    <!-- PRICES & STOCK -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-bold mb-2">Regular Price ($)</label>
                            <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="w-full border p-2 rounded" required>
                        </div>
                        
                        <!-- SALE PRICE FIELD -->
                        <div>
                            <label class="block text-sm font-bold mb-2 text-red-600">Sale Price ($)</label>
                            <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="w-full border p-2 rounded border-red-200 focus:border-red-500" placeholder="Optional">
                            <p class="text-[10px] text-gray-400 mt-1">Must be lower than Regular Price.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2">Total Stock</label>
                            <input type="number" name="stock_quantity" :value="currentStock" @input="if(!hasVariants) manualStock = $event.target.value" :readonly="hasVariants" :class="hasVariants ? 'bg-gray-100 cursor-not-allowed text-gray-500' : 'bg-white'" class="w-full border p-2 rounded transition" required>
                        </div>
                    </div>

                    <!-- CLASSIFICATION -->
                    <div class="space-y-4 bg-gray-50 p-4 rounded border">
                        <h3 class="font-bold text-gray-700 border-b pb-2 mb-2">Classification & Inventory</h3>
                        
                        <!-- CATEGORIES -->
                        <div x-data="{ 
                            selected: {{ json_encode($product->categories ? $product->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values() : []) }}, 
                            newCat: '' 
                        }">
                             <label class="block text-sm font-bold mb-2">Categories</label>
                             <div class="flex flex-wrap gap-2 mb-2 p-2 border rounded bg-white min-h-[42px]">
                                <template x-for="(cat, index) in selected" :key="index">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded flex items-center">
                                        <span x-text="cat.name"></span>
                                        <input type="hidden" name="categories[]" :value="cat.id || cat.name">
                                        <button type="button" @click="selected.splice(index, 1)" class="ml-1 text-blue-600 font-bold hover:text-red-500">&times;</button>
                                    </span>
                                </template>
                                <input type="text" x-model="newCat" 
                                       @keydown.enter.prevent="if(newCat.trim()) { selected.push({name: newCat.trim()}); newCat = ''; }" 
                                       @blur="if(newCat.trim()) { selected.push({name: newCat.trim()}); newCat = ''; }" 
                                       placeholder="Add category..." 
                                       class="outline-none flex-1 text-sm bg-transparent min-w-[100px]">
                             </div>
                             <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                                @foreach($categories as $category)
                                <button type="button" 
                                        x-show="!selected.some(s => s.id === {{ $category->id }})" 
                                        @click="selected.push({id: {{ $category->id }}, name: '{{ $category->name }}'})" 
                                        class="bg-gray-200 hover:bg-gray-300 text-xs px-2 py-1 rounded">
                                    {{ $category->name }}
                                </button>
                                @endforeach
                             </div>
                        </div>

                        <!-- CLOTHING LINE -->
                        <div>
                            <label class="block text-sm font-bold mb-2">Clothing Line / Collection <span class="font-normal text-gray-400">(Optional)</span></label>
                            <input type="text" 
                                   name="clothing_line" 
                                   value="{{ old('clothing_line', optional($product->clothingLine)->name) }}" 
                                   list="lineList" 
                                   class="w-full border p-2 rounded" 
                                   placeholder="e.g. Summer 2025">
                            <datalist id="lineList">
                                @foreach($lines as $line)
                                <option value="{{ $line->name }}">
                                @endforeach
                            </datalist>
                        </div>

                        <!-- TYPE -->
                        <div>
                            <label class="block text-sm font-bold mb-2">Product Type</label>
                            <input type="text" 
                                   name="attributes_type" 
                                   value="{{ old('attributes_type', $product->attributes['type'] ?? '') }}" 
                                   list="typeList" 
                                   class="w-full border p-2 rounded" 
                                   required>
                            <datalist id="typeList">
                                @foreach($types as $type)
                                <option value="{{ $type->value }}">
                                @endforeach
                            </datalist>
                        </div>

                        <!-- VARIANTS -->
                        <div>
                            <div class="flex justify-between items-end mb-2">
                                <label class="block text-sm font-bold">Size Variants</label>
                                <button type="button" @click="addVariant()" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200 font-bold">+ Add Size</button>
                            </div>
                            <template x-if="hasVariants">
                                <div class="space-y-2">
                                    <template x-for="(variant, index) in variants" :key="index">
                                        <div class="flex gap-2 items-center bg-white p-2 rounded border shadow-sm">
                                            <div class="w-1/2">
                                                <select :name="`variants[${index}][size]`" 
                                                        x-model="variant.size" 
                                                        class="w-full border p-1 rounded text-sm bg-gray-50 uppercase font-mono" 
                                                        required>
                                                    <option value="" disabled>Select Size</option>
                                                    @foreach($sizes as $sizeOption)
                                                        <option value="{{ $sizeOption->value }}">{{ $sizeOption->value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="w-1/3 relative">
                                                <input type="number" 
                                                       :name="`variants[${index}][stock]`" 
                                                       x-model="variant.stock" 
                                                       placeholder="0" 
                                                       class="w-full border p-1 rounded text-sm pl-8 font-bold" 
                                                       min="0" 
                                                       required>
                                                <span class="absolute left-2 top-1.5 text-gray-400 text-xs">Qty:</span>
                                            </div>
                                            <button type="button" @click="removeVariant(index)" class="text-red-400 hover:text-red-600 font-bold px-2">&times;</button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!hasVariants">
                                <div class="text-sm text-gray-400 italic p-3 border border-dashed rounded text-center bg-gray-50">
                                    No size variants added. Stock is managed globally.
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-2">Description</label>
                        <textarea name="description" class="w-full border p-2 rounded h-32">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>

                <!-- ПРАВАЯ КОЛОНКА (Images) -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-50 p-4 rounded border sticky top-4">
                        <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2">📸 Images <span class="text-xs font-normal text-gray-500">(Drag to reorder)</span></h3>
                        <input type="hidden" name="sorted_images_ids" id="sortedImagesIds">
                        <div id="existingImageList" class="space-y-2 mb-4 min-h-[20px]">
                            @foreach($product->images as $index => $img)
                                <div class="relative group bg-white p-2 rounded border flex items-center gap-3 cursor-move shadow-sm hover:shadow-md transition" data-id="{{ $img->id }}">
                                    <div class="text-gray-400 cursor-move px-1">☰</div>
                                    <img src="{{ $img->url }}" class="h-12 w-12 object-cover rounded border bg-gray-200">
                                    <div class="flex-1 text-xs">
                                        <div class="font-bold">Image #{{ $img->id }}</div>
                                        <div class="text-gray-400 order-badge">{{ $index === 0 ? 'Cover' : ($index + 1) }}</div>
                                    </div>
                                    <label class="cursor-pointer text-red-500 hover:bg-red-50 p-1 rounded">
                                        <input type="checkbox" name="deleted_images[]" value="{{ $img->id }}" class="hidden delete-checkbox" onchange="toggleDelete(this)">
                                        <span class="text-lg font-bold">&times;</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div id="newImagePreview" class="space-y-2 mb-4 border-t pt-4 border-dashed border-gray-300 hidden">
                            <p class="text-xs font-bold text-blue-600 uppercase">Ready to upload:</p>
                        </div>
                        <div class="mt-4">
                            <label class="block w-full cursor-pointer bg-blue-50 border-2 border-dashed border-blue-200 rounded p-4 text-center hover:bg-blue-100 transition">
                                <span class="text-sm font-bold text-blue-600">Click to Select Images</span>
                                <input type="file" name="{{ $product->exists ? 'new_images[]' : 'images[]' }}" multiple accept="image/*" class="hidden" onchange="handleFileSelect(event)">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center pt-6 border-t mt-6">
                <a href="{{ route('admin.products.index', ['tenant_id' => $currentTenantId]) }}" class="text-gray-500 hover:underline">Cancel</a>
                <button type="submit" class="bg-blue-600 text-white font-bold py-3 px-8 rounded hover:bg-blue-500 shadow-lg">
                    {{ $product->exists ? 'Update Product' : 'Create Product' }}
                </button>
            </div>
        </form>
    </div>

    <script>
        // Сортировка изображений
        const existingList = document.getElementById('existingImageList');
        if (existingList) {
            Sortable.create(existingList, {
                animation: 150,
                onEnd: function () { updateOrder(); }
            });
        }
        
        function updateOrder() {
            if (!existingList) return;
            const items = Array.from(existingList.querySelectorAll('[data-id]'));
            const ids = items.map(item => item.getAttribute('data-id'));
            document.getElementById('sortedImagesIds').value = ids.join(',');
            items.forEach((item, index) => {
                const badge = item.querySelector('.order-badge');
                if (badge) badge.innerText = index === 0 ? 'Cover' : (index + 1);
            });
        }
        
        function toggleDelete(checkbox) {
            const row = checkbox.closest('.group');
            row.style.opacity = checkbox.checked ? '0.4' : '1';
            row.style.backgroundColor = checkbox.checked ? '#fee2e2' : 'white';
        }
        
        function handleFileSelect(event) {
            const container = document.getElementById('newImagePreview');
            container.innerHTML = '<p class="text-xs font-bold text-blue-600 uppercase">Ready to upload:</p>';
            container.classList.remove('hidden');
            Array.from(event.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'bg-blue-50 p-2 rounded border border-blue-100 flex items-center gap-3';
                    div.innerHTML = `<img src="${e.target.result}" class="h-10 w-10 object-cover rounded border bg-white"><div class="text-xs text-blue-900 truncate flex-1">${file.name}</div><div class="text-xs text-gray-500 font-mono">${(file.size/1024).toFixed(0)}KB</div>`;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
        
        updateOrder();

        // Валидация формы
        document.addEventListener('alpine:initialized', () => {
            const form = document.getElementById('productForm');
            
            form.addEventListener('submit', function(e) {
                const stockInput = form.querySelector('input[name="stock_quantity"]');
                const hasVariants = Alpine.$data(form.querySelector('[x-data]')).hasVariants;
                
                if (!hasVariants) {
                    if (!stockInput.value || parseInt(stockInput.value) <= 0) {
                        e.preventDefault();
                        alert('Please enter a valid quantity for the product.');
                        return;
                    }
                }
                
                const variants = Alpine.$data(form.querySelector('[x-data]')).variants;
                let hasEmptyVariants = false;
                
                variants.forEach((variant, index) => {
                    if (variant.size && variant.size.trim() !== '' && (!variant.stock || parseInt(variant.stock) < 0)) {
                        hasEmptyVariants = true;
                    }
                });
                
                if (hasEmptyVariants) {
                    e.preventDefault();
                    alert('Please fill in all size variants with valid quantities.');
                    return;
                }
            });
        });
    </script>
@endif
@endsection