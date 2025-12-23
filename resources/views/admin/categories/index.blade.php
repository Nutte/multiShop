<!-- FILE: resources/views/admin/categories/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Categories')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Categories</h1>
        
        <div class="flex gap-4 items-center">
            <!-- SEARCH Filter -->
            <form method="GET" action="{{ route('admin.categories.index') }}" class="flex">
                @if(auth()->user()->role === 'super_admin')
                    <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="border p-2 rounded-l">
                <button class="bg-gray-200 px-3 rounded-r hover:bg-gray-300">🔍</button>
            </form>
        </div>
    </div>

    <!-- CREATE & CONTEXT TOOLBAR -->
    <div class="bg-white p-4 rounded shadow mb-6 border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Store Selector (Super Admin Only) -->
            @if(auth()->user()->role === 'super_admin')
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Store Context</label>
                    <form method="GET" action="{{ route('admin.categories.index') }}">
                        <select name="tenant_id" class="w-full border p-2 rounded bg-yellow-50 border-yellow-200" onchange="this.form.submit()">
                            @foreach(config('tenants.tenants') as $id => $data)
                                <option value="{{ $id }}" {{ $currentTenantId === $id ? 'selected' : '' }}>
                                    {{ $data['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <!-- Сохраняем поиск при смене магазина -->
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                    </form>
                </div>
            @endif

            <!-- Create Form -->
            <div>
                 <label class="block text-xs font-bold text-gray-500 mb-1">New Category</label>
                 <form action="{{ route('admin.categories.store') }}" method="POST" class="flex gap-2">
                    @csrf
                    @if(auth()->user()->role === 'super_admin')
                        <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">
                    @endif
                    <input type="text" name="name" placeholder="Category Name" class="w-full border p-2 rounded" required>
                    <button class="bg-blue-600 text-white px-4 rounded font-bold">Add</button>
                </form>
            </div>
        </div>
    </div>

    <!-- LIST -->
    <div class="bg-white rounded shadow p-6">
        <div class="flex flex-wrap gap-3">
            @forelse($categories as $cat)
                <div class="bg-gray-100 border rounded px-3 py-2 flex items-center gap-3 group hover:bg-gray-200 transition">
                    <!-- Simple Edit Form on Double Click logic could go here, sticking to simple display/delete for now as requested -->
                    <span class="font-bold text-gray-700">{{ $cat->name }}</span>
                    <span class="text-xs bg-white px-2 py-0.5 rounded text-gray-500 border">{{ $cat->products_count }} items</span>
                    
                    <form action="{{ route('admin.categories.destroy', ['category' => $cat->id, 'tenant_id' => $currentTenantId]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-300 group-hover:text-red-600 font-bold ml-2" onclick="return confirm('Delete category?')">&times;</button>
                    </form>
                </div>
            @empty
                <p class="text-gray-500 italic">No categories found.</p>
            @endforelse
        </div>
    </div>
@endsection