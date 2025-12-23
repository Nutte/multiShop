<!-- FILE: resources/views/admin/attributes/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Attributes')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Attributes (Tags & Sizes)</h1>
        
        <!-- Search Filter -->
        <form method="GET" action="{{ route('admin.attributes.index') }}" class="flex">
            @if(auth()->user()->role === 'super_admin')
                <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search value..." class="border p-2 rounded-l">
            <button class="bg-gray-200 px-3 rounded-r hover:bg-gray-300">🔍</button>
        </form>
    </div>

    @if(auth()->user()->role === 'super_admin')
        <div class="bg-white p-4 rounded shadow mb-6 border border-gray-200">
            <label class="block text-xs font-bold text-gray-500 mb-1">Store Context</label>
            <form method="GET" action="{{ route('admin.attributes.index') }}">
                <select name="tenant_id" class="w-full border p-2 rounded bg-yellow-50 border-yellow-200" onchange="this.form.submit()">
                    @foreach(config('tenants.tenants') as $id => $data)
                        <option value="{{ $id }}" {{ $currentTenantId === $id ? 'selected' : '' }}>
                            {{ $data['name'] }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- SIZES -->
        <div class="bg-white rounded shadow p-6 border-t-4 border-green-500">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">📏 Sizes</h2>
            
            <form action="{{ route('admin.attributes.store') }}" method="POST" class="flex gap-2 mb-4">
                @csrf
                @if(auth()->user()->role === 'super_admin')
                    <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">
                @endif
                <input type="hidden" name="type" value="size">
                <input type="text" name="value" placeholder="New Size (e.g. 3XL)" class="border p-2 rounded flex-1" required>
                <button class="bg-green-600 text-white px-4 rounded font-bold">+</button>
            </form>

            <div class="flex flex-wrap gap-2">
                @foreach($attributes['size'] ?? [] as $option)
                    <div class="bg-gray-100 px-3 py-1 rounded flex items-center gap-2 border">
                        <span class="font-mono font-bold">{{ $option->value }}</span>
                        <!-- ИСПРАВЛЕННЫЙ ROUTE -->
                        <form action="{{ route('admin.attributes.destroy', ['id' => $option->id, 'tenant_id' => $currentTenantId]) }}" method="POST">
                            @csrf 
                            @method('DELETE')
                            <button class="text-red-400 hover:text-red-600 font-bold">&times;</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TYPES -->
        <div class="bg-white rounded shadow p-6 border-t-4 border-blue-500">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">👕 Product Types</h2>
            
            <form action="{{ route('admin.attributes.store') }}" method="POST" class="flex gap-2 mb-4">
                @csrf
                @if(auth()->user()->role === 'super_admin')
                    <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">
                @endif
                <input type="hidden" name="type" value="product_type">
                <input type="text" name="value" placeholder="New Type (e.g. Jacket)" class="border p-2 rounded flex-1" required>
                <button class="bg-blue-600 text-white px-4 rounded font-bold">+</button>
            </form>

            <div class="flex flex-wrap gap-2">
                @foreach($attributes['product_type'] ?? [] as $option)
                    <div class="bg-blue-50 px-3 py-1 rounded flex items-center gap-2 border border-blue-100">
                        <span>{{ $option->value }}</span>
                        <!-- ИСПРАВЛЕННЫЙ ROUTE -->
                        <form action="{{ route('admin.attributes.destroy', ['id' => $option->id, 'tenant_id' => $currentTenantId]) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="text-red-400 hover:text-red-600 font-bold">&times;</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection