        <!-- FILE: resources/views/admin/products/index.blade.php -->
        @extends('layouts.admin')
        @section('title', 'Products')

        @section('content')
            <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Products Inventory</h1>
                
                <div class="flex gap-4 items-center">
                    <a href="{{ route('admin.products.create', ['tenant_id' => $currentTenantId]) }}" class="bg-blue-600 text-white px-4 py-2 rounded font-bold hover:bg-blue-500 shadow">
                        + Add Product
                    </a>
                </div>
            </div>

            <!-- Toolbar: Filter -->
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

            <!-- Product List -->
            <div class="bg-white rounded shadow overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="p-4 text-sm font-bold text-gray-600">Product</th>
                            <th class="p-4 text-sm font-bold text-gray-600">Price & Offers</th>
                            <th class="p-4 text-sm font-bold text-gray-600">Stock</th>
                            <th class="p-4 text-sm font-bold text-gray-600">Category</th>
                            @if(auth()->user()->role === 'super_admin' && !$currentTenantId)
                                <th class="p-4 text-sm font-bold text-gray-600">Store</th>
                            @endif
                            <th class="p-4 text-sm font-bold text-gray-600 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="border-b hover:bg-gray-50 group">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative">
                                            <img src="{{ $product->cover_url }}" class="h-12 w-12 rounded object-cover border bg-gray-100">
                                            <!-- PROMO BADGE (Если участвует в промокоде) -->
                                            @if($product->applicable_promos->isNotEmpty())
                                                <div class="absolute -top-2 -right-2 bg-purple-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full shadow border border-white" title="Active Promo Code">
                                                    PROMO
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-800">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $product->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    @if($product->has_discount)
                                        <div class="flex flex-col">
                                            <span class="text-red-600 font-bold">${{ $product->sale_price }}</span>
                                            <span class="text-xs text-gray-400 line-through">${{ $product->price }}</span>
                                        </div>
                                    @else
                                        <span class="font-bold">${{ $product->price }}</span>
                                    @endif

                                    <!-- Text info about Promo -->
                                    @foreach($product->applicable_promos as $promo)
                                        <div class="text-[10px] text-purple-600 font-bold mt-1 bg-purple-50 px-1 rounded inline-block">
                                            code: {{ $promo->code }}
                                        </div>
                                    @endforeach
                                </td>
                                <td class="p-4">
                                    @if($product->stock_quantity > 0)
                                        <span class="text-green-600 font-bold">{{ $product->stock_quantity }}</span>
                                        <span class="text-xs text-gray-400">in stock</span>
                                    @else
                                        <span class="text-red-500 font-bold">Out of Stock</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($product->categories as $cat)
                                            <span class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $cat->name }}</span>
                                        @endforeach
                                    </div>
                                    @if($product->clothingLine)
                                        <div class="mt-1 text-xs text-blue-500 font-bold">
                                            Line: {{ $product->clothingLine->name }}
                                        </div>
                                    @endif
                                </td>
                                @if(auth()->user()->role === 'super_admin' && !$currentTenantId)
                                    <td class="p-4 text-xs text-gray-500">{{ $product->tenant_name }}</td>
                                @endif
                                <td class="p-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ $product->preview_url }}" target="_blank" class="text-gray-400 hover:text-blue-600 p-1" title="Preview">
                                            👁️
                                        </a>
                                        <a href="{{ route('admin.products.edit', [$product->id, 'tenant_id' => $product->tenant_id ?? $currentTenantId]) }}" class="text-blue-600 hover:text-blue-800 p-1 font-bold">Edit</a>
                                        <form action="{{ route('admin.products.destroy', [$product->id, 'tenant_id' => $product->tenant_id ?? $currentTenantId]) }}" method="POST" onsubmit="return confirm('Delete this product?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-500 hover:text-red-700 p-1 font-bold">&times;</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-400 italic">No products found.</td>
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