<!-- FILE: resources/views/admin/users/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Users')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Customer Base</h1>
        
        <div class="flex gap-4 items-center">
            <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded font-bold hover:bg-blue-500 shadow">
                + Add Customer
            </a>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white p-4 rounded shadow mb-6 border border-gray-200">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row gap-4">
            
            @if(auth()->user()->role === 'super_admin')
                <div class="w-full md:w-64">
                    <select name="tenant_id" class="w-full border p-2 rounded bg-yellow-50 border-yellow-200" onchange="this.form.submit()">
                        <option value="">ALL CUSTOMERS</option>
                        @foreach(config('tenants.tenants') as $id => $data)
                            <option value="{{ $id }}" {{ request('tenant_id') === $id ? 'selected' : '' }}>
                                {{ $data['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex-1 flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Name, Phone (+380) or Email..." class="w-full border p-2 rounded">
                <button class="bg-gray-800 text-white px-4 py-2 rounded font-bold">Search</button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-4 text-sm font-bold text-gray-600">ID</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Customer</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Contact</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Store</th>
                    <th class="p-4 text-sm font-bold text-gray-600 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4 text-gray-500 text-sm">#{{ $user->id }}</td>
                        <td class="p-4">
                            <div class="font-bold text-gray-800">{{ $user->name }}</div>
                            <div class="text-xs text-gray-400">Reg: {{ $user->created_at->format('d M Y') }}</div>
                        </td>
                        <td class="p-4">
                            <!-- Отображаем телефон красиво -->
                            <div class="font-mono text-sm bg-gray-100 px-2 py-1 rounded inline-block mb-1">{{ $user->phone }}</div>
                            <div class="text-xs text-gray-500">{{ $user->email ?? 'No email' }}</div>
                        </td>
                        <td class="p-4">
                            @if($user->tenant_id)
                                <span class="text-xs font-bold uppercase bg-purple-100 text-purple-700 px-2 py-1 rounded">
                                    {{ config("tenants.tenants.{$user->tenant_id}.name") ?? $user->tenant_id }}
                                </span>
                            @else
                                <span class="text-xs font-bold uppercase bg-gray-200 text-gray-600 px-2 py-1 rounded">
                                    Global / Unassigned
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <!-- Явное указание ID, tenant_id не обязателен для ссылки, но можно передать для сохранения контекста -->
                                <a href="{{ route('admin.users.show', ['user' => $user->id]) }}" class="text-gray-500 hover:text-blue-600 text-sm font-bold">View</a>
                                <a href="{{ route('admin.users.edit', ['user' => $user->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm font-bold">Edit</a>
                                
                                @if(auth()->user()->role === 'super_admin')
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete user? This is irreversible.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-400 hover:text-red-600 text-sm font-bold ml-2">&times;</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-400 italic">No customers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
@endsection