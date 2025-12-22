<!-- FILE: resources/views/admin/products/edit.blade.php -->
@extends('layouts.admin')
@section('title', 'Edit Product')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit: {{ $product->name }}</h1>
        <a href="{{ $previewUrl }}" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-500 flex items-center gap-2">
            <span>👁️</span> Preview
        </a>
    </div>

    <div class="max-w-4xl mx-auto bg-white rounded shadow p-6">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Left -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold mb-2">Name</label>
                        <input type="text" name="name" value="{{ $product->name }}" class="w-full border p-2 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2">SKU</label>
                        <input type="text" name="sku" value="{{ $product->sku }}" class="w-full border p-2 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2">Price ($)</label>
                        <input type="number" step="0.01" name="price" value="{{ $product->price }}" class="w-full border p-2 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2">Stock</label>
                        <input type="number" name="stock_quantity" value="{{ $product->stock_quantity }}" class="w-full border p-2 rounded" required>
                    </div>
                </div>

                <!-- Right (Dynamic) -->
                <div class="space-y-4">
                    <!-- Categories -->
                    <div x-data="{ 
                        selected: {{ $product->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]) }}, 
                        options: {{ $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]) }},
                        newCat: '' 
                    }">
                        <label class="block text-sm font-bold mb-2">Categories</label>
                        <div class="flex flex-wrap gap-2 mb-2 p-2 border rounded min-h-[42px]">
                            <template x-for="(cat, index) in selected" :key="index">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded flex items-center">
                                    <span x-text="cat.name"></span>
                                    <input type="hidden" name="categories[]" :value="cat.id || cat.name">
                                    <button type="button" @click="selected.splice(index, 1)" class="ml-1 text-blue-600 font-bold">&times;</button>
                                </span>
                            </template>
                            <input type="text" x-model="newCat" @keydown.enter.prevent="if(newCat) { selected.push({name: newCat}); newCat = ''; }" placeholder="Add Category..." class="outline-none flex-1 text-sm bg-transparent">
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="opt in options">
                                <button type="button" @click="if(!selected.some(s => s.id === opt.id)) selected.push(opt)" class="bg-gray-100 hover:bg-gray-200 text-xs px-2 py-1 rounded" x-text="opt.name"></button>
                            </template>
                        </div>
                    </div>

                    <!-- Type -->
                    <div x-data="{ options: {{ $types->pluck('value') }} }">
                        <label class="block text-sm font-bold mb-2">Product Type</label>
                        <input type="text" name="attributes_type" value="{{ $product->attributes['type'] ?? '' }}" list="typeList" class="w-full border p-2 rounded" required>
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
                        <div class="flex flex-wrap gap-2 mb-2 p-2 border rounded min-h-[42px]">
                            <template x-for="(size, index) in selected" :key="index">
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded flex items-center">
                                    <span x-text="size"></span>
                                    <input type="hidden" name="attributes_size[]" :value="size">
                                    <button type="button" @click="selected.splice(index, 1)" class="ml-1 text-green-600 font-bold">&times;</button>
                                </span>
                            </template>
                            <input type="text" x-model="newSize" @keydown.enter.prevent="if(newSize) { selected.push(newSize); newSize = ''; }" placeholder="Add Size..." class="outline-none flex-1 text-sm bg-transparent">
                        </div>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="opt in options">
                                <button type="button" @click="if(!selected.includes(opt)) selected.push(opt)" class="bg-gray-100 hover:bg-gray-200 text-xs px-2 py-1 rounded" x-text="opt"></button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Description</label>
                <textarea name="description" class="w-full border p-2 rounded h-24">{{ $product->description }}</textarea>
            </div>
            
            @if($product->image_path)
                <div class="mb-4">
                    <p class="text-sm font-bold mb-1">Current Image:</p>
                    <img src="{{ $product->image_url }}" class="h-20 w-20 object-cover rounded border">
                </div>
            @endif

            <div class="mb-6">
                <label class="block text-sm font-bold mb-2">Change Image</label>
                <input type="file" name="image" class="w-full border p-2 rounded">
            </div>

            <button class="bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-500">Update Product</button>
        </form>
    </div>
@endsection