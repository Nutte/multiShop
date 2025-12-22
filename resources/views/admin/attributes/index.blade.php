<!-- FILE: resources/views/admin/attributes/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Attributes')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Attributes Manager</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- SIZES -->
        <div class="bg-white rounded shadow p-6">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                📏 Sizes
            </h2>
            
            <form action="{{ route('admin.attributes.store') }}" method="POST" class="flex gap-2 mb-4">
                @csrf
                <input type="hidden" name="type" value="size">
                <input type="text" name="value" placeholder="New Size (e.g. 3XL)" class="border p-2 rounded flex-1" required>
                <button class="bg-blue-600 text-white px-4 rounded font-bold">+</button>
            </form>

            <div class="flex flex-wrap gap-2">
                @foreach($attributes['size'] ?? [] as $option)
                    <div class="bg-gray-100 px-3 py-1 rounded flex items-center gap-2 border">
                        <span>{{ $option->value }}</span>
                        <form action="{{ route('admin.attributes.destroy', $option->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- PRODUCT TYPES -->
        <div class="bg-white rounded shadow p-6">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                👕 Product Types
            </h2>
            
            <form action="{{ route('admin.attributes.store') }}" method="POST" class="flex gap-2 mb-4">
                @csrf
                <input type="hidden" name="type" value="product_type">
                <input type="text" name="value" placeholder="New Type (e.g. Jacket)" class="border p-2 rounded flex-1" required>
                <button class="bg-blue-600 text-white px-4 rounded font-bold">+</button>
            </form>

            <div class="flex flex-wrap gap-2">
                @foreach($attributes['product_type'] ?? [] as $option)
                    <div class="bg-blue-50 px-3 py-1 rounded flex items-center gap-2 border border-blue-100">
                        <span>{{ $option->value }}</span>
                        <form action="{{ route('admin.attributes.destroy', $option->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection