<!-- FILE: resources/views/admin/clothing_lines/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Clothing Lines')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Clothing Lines / Collections</h1>
        
        <div class="flex gap-4 items-center">
            <!-- SEARCH Filter -->
            <form method="GET" action="{{ route('admin.clothing-lines.index') }}" class="flex">
                @if(auth()->user()->role === 'super_admin')
                    <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="border p-2 rounded-l">
                <button class="bg-gray-200 px-3 rounded-r hover:bg-gray-300">🔍</button>
            </form>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="bg-white p-4 rounded shadow mb-6 border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Store Selector (Super Admin Only) -->
            @if(auth()->user()->role === 'super_admin')
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Store Context</label>
                    <form method="GET" action="{{ route('admin.clothing-lines.index') }}">
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

            <!-- Create Form -->
            <div>
                 <label class="block text-xs font-bold text-gray-500 mb-1">New Collection</label>
                 <form action="{{ route('admin.clothing-lines.store') }}" method="POST" class="flex gap-2">
                    @csrf
                    @if(auth()->user()->role === 'super_admin')
                        <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">
                    @endif
                    <input type="text" name="name" placeholder="Collection Name (e.g. Summer 2025)" class="w-full border p-2 rounded" required>
                    <button class="bg-purple-600 text-white px-4 rounded font-bold">Add</button>
                </form>
            </div>
        </div>
    </div>

    <!-- LIST -->
    <div class="bg-white rounded shadow p-6">
        <div class="flex flex-wrap gap-3">
            @forelse($lines as $line)
                <div class="bg-purple-50 border border-purple-100 rounded px-3 py-2 flex items-center gap-3 group hover:bg-purple-100 transition">
                    <div>
                        <span class="font-bold text-gray-700 block">{{ $line->name }}</span>
                        <span class="text-xs text-gray-400">{{ $line->slug }}</span>
                    </div>
                    <span class="text-xs bg-white px-2 py-0.5 rounded text-gray-500 border">{{ $line->products_count }} items</span>
                    
                    <!-- ISPREVLENIE: Упрощенная передача параметров для избежания ParseError -->
                    <form action="{{ route('admin.clothing-lines.destroy', [$line->id, 'tenant_id' => $currentTenantId]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-300 group-hover:text-red-600 font-bold ml-2" onclick="return confirm('Delete collection?')">&times;</button>
                    </form>
                </div>
            @empty
                <p class="text-gray-500 italic">No collections found.</p>
            @endforelse
        </div>
    </div>
@endsection