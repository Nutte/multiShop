<!-- FILE: resources/views/admin/products/form.blade.php -->
@extends('layouts.admin')
@section('title', $title)

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ $title }}</h1>
        @if($previewUrl)
            <a href="{{ $previewUrl }}" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-500 flex items-center gap-2 font-bold shadow">
                <span>👁️</span> Preview on Site
            </a>
        @endif
    </div>

    <div class="max-w-4xl mx-auto bg-white rounded shadow p-6">
        <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif

            <!-- ВАЖНОЕ ИСПРАВЛЕНИЕ: Передаем контекст магазина -->
            <!-- Без этого поля при сохранении/обновлении контроллер не знает, в какой БД искать товар -->
            @if(isset($currentTenantId))
                <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">
            @endif

            <!-- ВЫБОР МАГАЗИНА (Только для Супер-Админа и только при СОЗДАНИИ НОВОГО) -->
            @if(auth()->user()->role === 'super_admin' && !$product->exists)
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                    <label class="block text-sm font-bold mb-2 text-yellow-800">Target Store (Super Admin)</label>
                    <select name="target_tenant" class="w-full border p-2 rounded bg-white">
                        @foreach(config('tenants.tenants') as $id => $data)
                            <option value="{{ $id }}" {{ $currentTenantId == $id ? 'selected' : '' }}>
                                {{ $data['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Основные поля -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold mb-2">Name</label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full border p-2 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full border p-2 rounded" required>
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
                </div>

                <!-- Динамические поля (Alpine.js) -->
                <div class="space-y-4 bg-gray-50 p-4 rounded border">
                    <h3 class="font-bold text-gray-700 border-b pb-2 mb-2">Classification</h3>
                    
                    <!-- Categories -->
                    <div x-data="{ 
                        selected: {{ json_encode($product->categories ? $product->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values() : []) }}, 
                        options: {{ $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values() }},
                        newCat: '' 
                    }">
                        <label class="block text-sm font-bold mb-2">Categories <span class="text-gray-400 font-normal">(Select or Type New)</span></label>
                        
                        <!-- Список выбранных -->
                        <div class="flex flex-wrap gap-2 mb-2 p-2 border rounded bg-white min-h-[42px]">
                            <template x-for="(cat, index) in selected" :key="index">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded flex items-center">
                                    <span x-text="cat.name"></span>
                                    <!-- ВАЖНО: Если id нет (новая), отправляем имя. Контроллер сам создаст. -->
                                    <input type="hidden" name="categories[]" :value="cat.id || cat.name">
                                    <button type="button" @click="selected.splice(index, 1)" class="ml-1 text-blue-600 font-bold hover:text-red-500">&times;</button>
                                </span>
                            </template>
                            
                            <!-- Инпут для добавления -->
                            <input type="text" 
                                   x-model="newCat" 
                                   @keydown.enter.prevent="if(newCat.trim()) { selected.push({name: newCat.trim()}); newCat = ''; }" 
                                   @blur="if(newCat.trim()) { selected.push({name: newCat.trim()}); newCat = ''; }"
                                   placeholder="Add..." 
                                   class="outline-none flex-1 text-sm bg-transparent min-w-[100px]">
                        </div>

                        <!-- Подсказки (Существующие категории) -->
                        <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                            <template x-for="opt in options">
                                <button type="button" 
                                        x-show="!selected.some(s => s.id === opt.id)"
                                        @click="selected.push(opt)" 
                                        class="bg-gray-200 hover:bg-gray-300 text-xs px-2 py-1 rounded transition" 
                                        x-text="opt.name">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Type -->
                    <div x-data="{ options: {{ $types->pluck('value') }} }">
                        <label class="block text-sm font-bold mb-2">Product Type</label>
                        <input type="text" name="attributes_type" value="{{ old('attributes_type', $product->attributes['type'] ?? '') }}" list="typeList" class="w-full border p-2 rounded" required placeholder="e.g. Hoodie">
                        <datalist id="typeList">
                            <template x-for="opt in options">
                                <option :value="opt"></option>
                            </template>
                        </datalist>
                    </div>

                    <!-- Sizes -->
                    <div x-data="{ 
                        selected: {{ json_encode($product->attributes['size'] ?? []) }}, 
                        newSize: '',
                        options: {{ $sizes->pluck('value') }}
                    }">
                        <label class="block text-sm font-bold mb-2">Sizes</label>
                        <div class="flex flex-wrap gap-2 mb-2 p-2 border rounded bg-white min-h-[42px]">
                            <template x-for="(size, index) in selected" :key="index">
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded flex items-center">
                                    <span x-text="size"></span>
                                    <input type="hidden" name="attributes_size[]" :value="size">
                                    <button type="button" @click="selected.splice(index, 1)" class="ml-1 text-green-600 font-bold hover:text-red-500">&times;</button>
                                </span>
                            </template>
                            <input type="text" x-model="newSize" @keydown.enter.prevent="if(newSize.trim()) { selected.push(newSize.trim()); newSize = ''; }" placeholder="Add..." class="outline-none flex-1 text-sm bg-transparent">
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="opt in options">
                                <button type="button" @click="if(!selected.includes(opt)) selected.push(opt)" class="bg-gray-200 hover:bg-gray-300 text-xs px-2 py-1 rounded transition" x-text="opt"></button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold mb-2">Description</label>
                <textarea name="description" class="w-full border p-2 rounded h-32">{{ old('description', $product->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-bold mb-2">Product Image</label>
                    <input type="file" name="image" class="w-full border p-2 rounded">
                </div>
                @if($product->image_path)
                    <div class="border rounded p-2 text-center bg-gray-50">
                        <p class="text-xs font-bold mb-1 text-gray-500">Current Image</p>
                        <img src="{{ $product->image_url }}" class="h-32 w-auto mx-auto object-contain rounded">
                    </div>
                @endif
            </div>

            <div class="flex justify-between items-center pt-6 border-t">
                <!-- Ссылка отмены также должна знать контекст -->
                <a href="{{ route('admin.products.index', ['tenant_id' => $currentTenantId]) }}" class="text-gray-500 hover:underline">Cancel</a>
                <button class="bg-blue-600 text-white font-bold py-3 px-8 rounded hover:bg-blue-500 shadow-lg transition transform hover:-translate-y-0.5">
                    {{ $product->exists ? 'Update Product' : 'Create Product' }}
                </button>
            </div>
        </form>
    </div>
@endsection