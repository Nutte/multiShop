<!-- FILE: resources/views/admin/products/create.blade.php -->
@extends('layouts.admin')
@section('title', 'Add Product')

@section('content')
    <div class="max-w-4xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-2xl font-bold mb-6">Add New Product</h1>
        
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- SUPER ADMIN SELECTOR -->
            @if(auth()->user()->role === 'super_admin')
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                    <label class="block text-sm font-bold mb-2 text-yellow-800">Target Store</label>
                    <select name="target_tenant" class="w-full border p-2 rounded bg-white">
                        @foreach(config('tenants.tenants') as $id => $data)
                            <option value="{{ $id }}" {{ session('admin_current_tenant_id') == $id ? 'selected' : '' }}>
                                {{ $data['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- LEft Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold mb-2">Name</label>
                        <input type="text" name="name" class="w-full border p-2 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2">SKU</label>
                        <input type="text" name="sku" class="w-full border p-2 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2">Price ($)</label>
                        <input type="number" step="0.01" name="price" class="w-full border p-2 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2">Stock</label>
                        <input type="number" name="stock_quantity" class="w-full border p-2 rounded" required>
                    </div>
                </div>

                <!-- Right Column (Dynamic) -->
                <div class="space-y-4">
                    <!-- Categories (Multi-select + Create) -->
                    <div x-data="{ 
                        selected: [], 
                        options: {{ $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]) }},
                        newCat: '' 
                    }">
                        <label class="block text-sm font-bold mb-2">Categories (Select or Type New)</label>
                        <div class="flex flex-wrap gap-2 mb-2 p-2 border rounded min-h-[42px]">
                            <template x-for="(cat, index) in selected" :key="index">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded flex items-center">
                                    <span x-text="cat.name"></span>
                                    <input type="hidden" name="categories[]" :value="cat.id || cat.name">
                                    <button type="button" @click="selected.splice(index, 1)" class="ml-1 text-blue-600 font-bold">&times;</button>
                                </span>
                            </template>
                            <input type="text" x-model="newCat" @keydown.enter.prevent="if(newCat) { selected.push({name: newCat}); newCat = ''; }" placeholder="Type & Enter..." class="outline-none flex-1 text-sm bg-transparent">
                        </div>
                        <div class="text-xs text-gray-500 mb-2">Existing:</div>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="opt in options">
                                <button type="button" @click="if(!selected.some(s => s.id === opt.id)) selected.push(opt)" class="bg-gray-100 hover:bg-gray-200 text-xs px-2 py-1 rounded" x-text="opt.name"></button>
                            </template>
                        </div>
                    </div>

                    <!-- Type (Create on fly) -->
                    <div x-data="{ val: '', options: {{ $types->pluck('value') }} }">
                        <label class="block text-sm font-bold mb-2">Product Type</label>
                        <input type="text" name="attributes_type" list="typeList" class="w-full border p-2 rounded" required placeholder="e.g. Hoodie">
                        <datalist id="typeList">
                            <template x-for="opt in options">
                                <option :value="opt"></option>
                            </template>
                        </datalist>
                    </div>

                    <!-- Sizes (Multi-select + Create) -->
                    <div x-data="{ 
                        selected: [], 
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
                <textarea name="description" class="w-full border p-2 rounded h-24"></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold mb-2">Image</label>
                <input type="file" name="image" class="w-full border p-2 rounded">
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.products.index') }}" class="px-4 py-2 text-gray-500 hover:underline">Cancel</a>
                <button class="bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-500">Save Product</button>
            </div>
        </form>
    </div>
@endsection