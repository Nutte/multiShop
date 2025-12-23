<!-- FILE: resources/views/admin/products/form.blade.php -->
@extends('layouts.admin')
@section('title', $title)

@section('content')
    <!-- Подключаем SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ $title }}</h1>
        @if($previewUrl)
            <a href="{{ $previewUrl }}" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-500 flex items-center gap-2 font-bold shadow">
                <span>👁️</span> Preview on Site
            </a>
        @endif
    </div>

    <div class="max-w-6xl mx-auto bg-white rounded shadow p-6">
        <form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="productForm">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif

            @if(isset($currentTenantId))
                <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">
            @endif

            <!-- ВЫБОР МАГАЗИНА -->
            @if(auth()->user()->role === 'super_admin' && !$product->exists)
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                    <label class="block text-sm font-bold mb-2 text-yellow-800">Target Store</label>
                    <select name="target_tenant" class="w-full border p-2 rounded bg-white">
                        @foreach(config('tenants.tenants') as $id => $data)
                            <option value="{{ $id }}" {{ $currentTenantId == $id ? 'selected' : '' }}>
                                {{ $data['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- ЛЕВАЯ КОЛОНКА (Инпуты) -->
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
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold mb-2">Price ($)</label>
                            <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="w-full border p-2 rounded" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2">Stock</label>
                            <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" class="w-full border p-2 rounded" required>
                        </div>
                    </div>

                    <!-- Alpine Categories & Attributes -->
                    <div class="space-y-4 bg-gray-50 p-4 rounded border">
                        <h3 class="font-bold text-gray-700 border-b pb-2 mb-2">Classification</h3>
                        <!-- Categories -->
                        <div x-data="{ selected: {{ json_encode($product->categories ? $product->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values() : []) }}, options: {{ $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values() }}, newCat: '' }">
                             <label class="block text-sm font-bold mb-2">Categories</label>
                             <div class="flex flex-wrap gap-2 mb-2 p-2 border rounded bg-white min-h-[42px]">
                                <template x-for="(cat, index) in selected" :key="index">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded flex items-center"><span x-text="cat.name"></span><input type="hidden" name="categories[]" :value="cat.id || cat.name"><button type="button" @click="selected.splice(index, 1)" class="ml-1 text-blue-600 font-bold">&times;</button></span>
                                </template>
                                <input type="text" x-model="newCat" @keydown.enter.prevent="if(newCat.trim()) { selected.push({name: newCat.trim()}); newCat = ''; }" @blur="if(newCat.trim()) { selected.push({name: newCat.trim()}); newCat = ''; }" placeholder="Add..." class="outline-none flex-1 text-sm bg-transparent min-w-[100px]">
                             </div>
                             <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto"><template x-for="opt in options"><button type="button" x-show="!selected.some(s => s.id === opt.id)" @click="selected.push(opt)" class="bg-gray-200 hover:bg-gray-300 text-xs px-2 py-1 rounded" x-text="opt.name"></button></template></div>
                        </div>
                        <!-- Type -->
                        <div x-data="{ options: {{ $types->pluck('value') }} }">
                            <label class="block text-sm font-bold mb-2">Product Type</label>
                            <input type="text" name="attributes_type" value="{{ old('attributes_type', $product->attributes['type'] ?? '') }}" list="typeList" class="w-full border p-2 rounded" required>
                            <datalist id="typeList"><template x-for="opt in options"><option :value="opt"></option></template></datalist>
                        </div>
                        <!-- Sizes -->
                        <div x-data="{ selected: {{ json_encode($product->attributes['size'] ?? []) }}, newSize: '', options: {{ $sizes->pluck('value') }} }">
                             <label class="block text-sm font-bold mb-2">Sizes</label>
                             <div class="flex flex-wrap gap-2 mb-2 p-2 border rounded bg-white min-h-[42px]">
                                <template x-for="(size, index) in selected" :key="index"><span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded flex items-center"><span x-text="size"></span><input type="hidden" name="attributes_size[]" :value="size"><button type="button" @click="selected.splice(index, 1)" class="ml-1 text-green-600 font-bold">&times;</button></span></template>
                                <input type="text" x-model="newSize" @keydown.enter.prevent="if(newSize.trim()) { selected.push(newSize.trim()); newSize = ''; }" placeholder="Add..." class="outline-none flex-1 text-sm bg-transparent">
                             </div>
                             <div class="flex flex-wrap gap-1"><template x-for="opt in options"><button type="button" @click="if(!selected.includes(opt)) selected.push(opt)" class="bg-gray-200 hover:bg-gray-300 text-xs px-2 py-1 rounded" x-text="opt"></button></template></div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold mb-2">Description</label>
                        <textarea name="description" class="w-full border p-2 rounded h-32">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>

                <!-- ПРАВАЯ КОЛОНКА: Изображения -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-50 p-4 rounded border sticky top-4">
                        <h3 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
                            📸 Images
                            <span class="text-xs font-normal text-gray-500">(Drag to reorder)</span>
                        </h3>

                        <!-- Input для сохранения порядка существующих -->
                        <input type="hidden" name="sorted_images_ids" id="sortedImagesIds">

                        <!-- 1. Список СУЩЕСТВУЮЩИХ (Сортируемый) -->
                        <div id="existingImageList" class="space-y-2 mb-4 min-h-[20px]">
                            @foreach($product->images as $index => $img)
                                <div class="relative group bg-white p-2 rounded border flex items-center gap-3 cursor-move shadow-sm hover:shadow-md transition" data-id="{{ $img->id }}">
                                    <!-- Handle -->
                                    <div class="text-gray-400 cursor-move px-1">☰</div>
                                    <!-- Thumb -->
                                    <img src="{{ $img->url }}" class="h-12 w-12 object-cover rounded border bg-gray-200">
                                    <!-- Meta -->
                                    <div class="flex-1 text-xs">
                                        <div class="font-bold">Image #{{ $img->id }}</div>
                                        <div class="text-gray-400 order-badge">{{ $index === 0 ? 'Cover' : ($index + 1) }}</div>
                                    </div>
                                    <!-- Delete -->
                                    <label class="cursor-pointer text-red-500 hover:bg-red-50 p-1 rounded">
                                        <input type="checkbox" name="deleted_images[]" value="{{ $img->id }}" class="hidden delete-checkbox" onchange="toggleDelete(this)">
                                        <span class="text-lg font-bold">&times;</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <!-- 2. Превью НОВЫХ (Не сортируемые, просто список) -->
                        <div id="newImagePreview" class="space-y-2 mb-4 border-t pt-4 border-dashed border-gray-300 hidden">
                            <p class="text-xs font-bold text-blue-600 uppercase">Ready to upload:</p>
                            <!-- Сюда JS добавит картинки -->
                        </div>

                        <!-- 3. Кнопка загрузки -->
                        <div class="mt-4">
                            <label class="block w-full cursor-pointer bg-blue-50 border-2 border-dashed border-blue-200 rounded p-4 text-center hover:bg-blue-100 transition">
                                <span class="text-sm font-bold text-blue-600">Click to Select Images</span>
                                <input type="file" 
                                       id="fileInput"
                                       name="{{ $product->exists ? 'new_images[]' : 'images[]' }}" 
                                       multiple 
                                       accept="image/*"
                                       class="hidden"
                                       onchange="handleFileSelect(event)">
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center pt-6 border-t mt-6">
                <a href="{{ route('admin.products.index', ['tenant_id' => $currentTenantId]) }}" class="text-gray-500 hover:underline">Cancel</a>
                <button class="bg-blue-600 text-white font-bold py-3 px-8 rounded hover:bg-blue-500 shadow-lg" onclick="updateOrder()">
                    {{ $product->exists ? 'Update Product' : 'Create Product' }}
                </button>
            </div>
        </form>
    </div>

    <script>
        // 1. SortableJS для существующих картинок
        const existingList = document.getElementById('existingImageList');
        if (existingList) {
            Sortable.create(existingList, {
                animation: 150,
                onEnd: function () {
                    updateOrder();
                }
            });
        }

        function updateOrder() {
            if (!existingList) return;
            const items = Array.from(existingList.querySelectorAll('[data-id]'));
            const ids = items.map(item => item.getAttribute('data-id'));
            document.getElementById('sortedImagesIds').value = ids.join(',');

            // Обновляем визуальные бейджики (Cover, 2, 3...)
            items.forEach((item, index) => {
                const badge = item.querySelector('.order-badge');
                if (badge) badge.innerText = index === 0 ? 'Cover' : (index + 1);
            });
        }

        function toggleDelete(checkbox) {
            const row = checkbox.closest('.group');
            if (checkbox.checked) {
                row.style.opacity = '0.4';
                row.style.backgroundColor = '#fee2e2'; // red-100
            } else {
                row.style.opacity = '1';
                row.style.backgroundColor = 'white';
            }
        }

        // 2. Предпросмотр НОВЫХ картинок
        function handleFileSelect(event) {
            const container = document.getElementById('newImagePreview');
            container.innerHTML = '<p class="text-xs font-bold text-blue-600 uppercase">Ready to upload:</p>'; // Clear prev
            container.classList.remove('hidden');

            const files = event.target.files;
            
            if (files.length === 0) {
                container.classList.add('hidden');
                return;
            }

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'bg-blue-50 p-2 rounded border border-blue-100 flex items-center gap-3';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="h-10 w-10 object-cover rounded border bg-white">
                        <div class="text-xs text-blue-900 truncate flex-1">${file.name}</div>
                        <div class="text-xs text-gray-500 font-mono">${(file.size / 1024).toFixed(0)}KB</div>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        // Init
        updateOrder();
    </script>
@endsection