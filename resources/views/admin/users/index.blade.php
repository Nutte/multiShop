<!-- FILE: resources/views/admin/users/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Customers')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Customer Management</h1>
    </div>

    <!-- FILTERS -->
    <div class="bg-white p-4 rounded shadow mb-6 border border-gray-200">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-4 items-end">
            
            {{-- Выбор магазина (Только для Супер-Админа) --}}
            @if(auth()->user()->role === 'super_admin')
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Store Context</label>
                    <select name="tenant_id" class="border p-2 rounded bg-yellow-50 font-bold text-gray-800" onchange="this.form.submit()">
                        @foreach(config('tenants.tenants') as $id => $data)
                            <option value="{{ $id }}" {{ $currentTenantId == $id ? 'selected' : '' }}>
                                🏪 {{ $data['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex-1">
                <label class="block text-xs font-bold text-gray-500 mb-1">Search Customer</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Email or Phone (+380...)" class="border p-2 rounded w-full">
            </div>

            <button class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-500">
                Find
            </button>
        </form>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-4 font-bold text-gray-600">ID</th>
                    <th class="p-4 font-bold text-gray-600">Name</th>
                    <th class="p-4 font-bold text-gray-600">Contact Info</th>
                    <th class="p-4 font-bold text-gray-600">Registered</th>
                    <th class="p-4 font-bold text-gray-600 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4 text-gray-500">#{{ $user->id }}</td>
                        <td class="p-4 font-bold">
                            {{ $user->name }}
                        </td>
                        <td class="p-4">
                            <div class="font-mono text-sm">{{ $user->phone }}</div>
                            <div class="text-xs text-gray-500">{{ $user->email }}</div>
                        </td>
                        <td class="p-4 text-sm text-gray-500">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.users.show', ['user' => $user->id, 'tenant_id' => $currentTenantId]) }}" 
                               class="bg-gray-800 text-white px-3 py-1 rounded text-sm font-bold hover:bg-gray-700">
                                Profile & Orders
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-400">No customers found in this store.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $users->withQueryString()->links() }}</div>
    </div>
@endsection