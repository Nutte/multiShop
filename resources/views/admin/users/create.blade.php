<!-- FILE: resources/views/admin/users/create.blade.php -->
@extends('layouts.admin')
@section('title', 'Create User')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Create New User</h1>
    
    <div class="bg-white rounded shadow p-6 max-w-md">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Name</label>
                <input type="text" name="name" class="w-full border p-2 rounded" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" class="w-full border p-2 rounded" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" class="w-full border p-2 rounded" required>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold mb-2">Role</label>
                <select name="role" class="w-full border p-2 rounded">
                    <option value="manager">Manager</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('admin.users.index') }}" class="text-gray-500 py-2">Cancel</a>
                <button class="bg-blue-600 text-white font-bold py-2 px-4 rounded">Create User</button>
            </div>
        </form>
    </div>
@endsection