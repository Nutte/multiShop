<!-- FILE: resources/views/admin/users/edit.blade.php -->
@extends('layouts.admin')
@section('title', 'Edit User')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit User: {{ $user->name }}</h1>
        @if($user->id !== auth()->id())
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button class="text-red-600 hover:text-red-800 hover:underline text-sm font-bold">Delete User</button>
            </form>
        @endif
    </div>
    
    <div class="bg-white rounded shadow p-6 max-w-md">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" x-data="{ role: '{{ $user->role }}' }">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Name</label>
                <input type="text" name="name" value="{{ $user->name }}" class="w-full border p-2 rounded" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" value="{{ $user->email }}" class="w-full border p-2 rounded" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Password <span class="text-gray-400 font-normal">(Leave blank to keep current)</span></label>
                <input type="password" name="password" class="w-full border p-2 rounded" placeholder="••••••">
            </div>

            <div class="mb-4 bg-gray-50 p-4 rounded border">
                <label class="block text-sm font-bold mb-2">System Role</label>
                <select name="role" x-model="role" class="w-full border p-2 rounded bg-white">
                    <option value="manager">Manager</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>

            <!-- Выбор магазина -->
            <div class="mb-6" x-show="role === 'manager'" x-transition>
                <label class="block text-sm font-bold mb-2 text-blue-800">Assigned Store</label>
                <select name="tenant_id" class="w-full border-2 border-blue-200 p-2 rounded bg-blue-50">
                    <option value="" disabled>-- Select a Store --</option>
                    @foreach($tenants as $id => $data)
                        <option value="{{ $id }}" {{ $user->tenant_id === $id ? 'selected' : '' }}>
                            {{ $data['name'] }}
                        </option>
                    @endforeach
                </select>
                @if($user->tenant_id && !array_key_exists($user->tenant_id, $tenants))
                    <p class="text-xs text-red-500 mt-1">Warning: Current store ID '{{ $user->tenant_id }}' not found in config.</p>
                @endif
            </div>

            <div class="flex justify-between items-center mt-6">
                <a href="{{ route('admin.users.index') }}" class="text-gray-500 py-2 hover:underline">Cancel</a>
                <button class="bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-500 shadow">
                    Update User
                </button>
            </div>
        </form>
    </div>
@endsection