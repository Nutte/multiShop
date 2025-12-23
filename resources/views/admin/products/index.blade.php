<!-- FILE: resources/views/admin/products/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Products')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold">Products Manager</h1>
        <!-- При создании всегда перекидываем на форму (контроллер выберет дефолтный магазин если надо) -->
        <a href="{{ route('admin.products.create', ['tenant_id' => $currentTenantId]) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500 flex items-center gap-2">
            <span>+</span> Add Product
        </a>
    </div>

    <!-- FILTERS PANEL -->
    <div class="bg-white p-4 rounded shadow mb-6 border border-gray-200">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            
            <!-- 1. STORE FILTER (Super Admin Only) -->
            @if(auth()->user()->role === 'super_admin')
                <div class="col-span-1">
                    <label class="block text-xs font-bold text-gray-500 mb-1">Store Context</label>
                    <select name="tenant_id" class="w-full border p-2 rounded bg-yellow-50 border-yellow-200" onchange="this.form.submit()">
                        <option value="">ALL STORES (Overview)</option>
                        @foreach(config('tenants.tenants') as $id => $data)
                            <option value="{{ $id }}" {{ $currentTenantId === $id ? 'selected' : '' }}>
                                {{ $data['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- 2. SEARCH -->
            <div class="{{ auth()->user()->role === 'super_admin' ? 'col-span-1' : 'col-span-2' }}">
                <label class="block text-xs font-bold text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or SKU..." class="w-full border p-2 rounded">
            </div>

            <!-- Фильтры активны только если выбран конкретный магазин (так как категории у всех разные) -->
            @if($currentTenantId)
                <!-- 3. CATEGORY -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Category</label>
                    <select name="category_id" class="w-full border p-2 rounded" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. ATTRIBUTES -->
                <div class="flex gap-2">
                    <div class="w-1/2">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Type</label>
                        <select name="type" class="w-full border p-2 rounded" onchange="this.form.submit()">
                            <option value="">Any</option>
                            @foreach($types as $t)
                                <option value="{{ $t->value }}" {{ request('type') == $t->value ? 'selected' : '' }}>{{ $t->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-1/2">
                        <label class="block text-xs font-bold text-gray-500 mb-1">Size</label>
                        <select name="size" class="w-full border p-2 rounded" onchange="this.form.submit()">
                            <option value="">Any</option>
                            @foreach($sizes as $s)
                                <option value="{{ $s->value }}" {{ request('size') == $s->value ? 'selected' : '' }}>{{ $s->value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @else
                <div class="col-span-2 flex items-center text-xs text-gray-400 italic">
                    Select a specific store to filter by Category/Attributes.
                </div>
            @endif

            <!-- 5. SUBMIT -->
            <div class="flex items-end">
                <button class="w-full bg-gray-800 text-white p-2 rounded hover:bg-gray-700 font-bold">Filter</button>
            </div>
        </form>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold uppercase bg-gray-200 px-2 py-1 rounded">{{ $product->tenant_name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <img src="{{ $product->image_url }}" alt="" class="h-10 w-10 rounded object-cover border mr-3">
                                <div>
                                    <div class="font-bold text-gray-900">{{ $product->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $product->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500">
                            <div>Type: <b>{{ $product->attributes['type'] ?? '-' }}</b></div>
                            <div class="mt-1">
                                @foreach($product->categories as $cat)
                                    <span class="bg-blue-50 text-blue-700 px-1 rounded border border-blue-100">{{ $cat->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 font-bold">${{ $product->price }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ $product->preview_url }}" target="_blank" class="text-green-600 hover:text-green-800 mr-3 font-bold">👁️</a>
                            <!-- Передаем tenant_id, чтобы контроллер знал, в какой базе искать -->
                            <a href="{{ route('admin.products.edit', ['product' => $product->id, 'tenant_id' => $product->tenant_id]) }}" class="text-blue-600 hover:text-blue-800 mr-3">Edit</a>
                            
                            <form action="{{ route('admin.products.destroy', ['product' => $product->id, 'tenant_id' => $product->tenant_id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:text-red-800">Del</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            No products found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Пагинация показывается только если это не коллекция ALL -->
        @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="p-4 border-t">
                {{ $products->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection