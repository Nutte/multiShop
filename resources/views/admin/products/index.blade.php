<!-- FILE: resources/views/admin/products/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Products')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Products</h1>
        <a href="{{ route('admin.products.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500 flex items-center gap-2">
            <span>+</span> Add Product
        </a>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU / Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categories</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($products as $product)
                    <tr>
                        <td class="px-6 py-4">
                            <img src="{{ $product->image_url }}" alt="" class="h-10 w-10 rounded object-cover border">
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900">{{ $product->name }}</div>
                            <div class="text-xs text-gray-500">{{ $product->sku }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @foreach($product->categories as $cat)
                                <span class="bg-gray-100 text-xs px-2 py-1 rounded">{{ $cat->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 font-bold">${{ $product->price }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-bold {{ $product->stock_quantity > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $product->stock_quantity }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ $product->preview_url }}" target="_blank" class="text-green-600 hover:underline mr-2 font-bold" title="Preview on site">👁️</a>
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="text-blue-600 hover:underline mr-2">Edit</a>
                            
                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">
            {{ $products->links() }}
        </div>
    </div>
@endsection