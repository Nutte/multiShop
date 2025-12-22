<!-- FILE: resources/views/admin/users/edit.blade.php -->
@extends('layouts.admin')
@section('title', 'Edit User')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit User: {{ $user->name }}</h1>
        @if($user->id !== auth()->id())
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                @csrf
                @method('DELETE')
                <button class="text-red-600 hover:underline text-sm">Delete User</button>
            </form>
        @endif
    </div>
    
    <div class="bg-white rounded shadow p-6 max-w-md">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
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
                <label class="block text-sm font-bold mb-2">Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="w-full border p-2 rounded">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold mb-2">Role</label>
                <select name="role" class="w-full border p-2 rounded">
                    <option value="manager" {{ $user->role == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="super_admin" {{ $user->role == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('admin.users.index') }}" class="text-gray-500 py-2">Cancel</a>
                <button class="bg-blue-600 text-white font-bold py-2 px-4 rounded">Update User</button>
            </div>
        </form>
    </div>
@endsection