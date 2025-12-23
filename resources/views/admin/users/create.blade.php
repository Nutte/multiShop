<!-- FILE: resources/views/admin/users/create.blade.php -->
@extends('layouts.admin')
@section('title', 'Create User')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Create New User</h1>
    
    <div class="bg-white rounded shadow p-6 max-w-md">
        <form action="{{ route('admin.users.store') }}" method="POST" x-data="{ role: 'manager' }">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Name</label>
                <input type="text" name="name" class="w-full border p-2 rounded" required value="{{ old('name') }}">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" class="w-full border p-2 rounded" required value="{{ old('email') }}">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" class="w-full border p-2 rounded" required>
            </div>

            <div class="mb-4 bg-gray-50 p-4 rounded border">
                <label class="block text-sm font-bold mb-2">System Role</label>
                <select name="role" x-model="role" class="w-full border p-2 rounded bg-white">
                    <option value="manager">Manager (Store Specific)</option>
                    <option value="super_admin">Super Admin (Global Access)</option>
                </select>
                
                <p class="text-xs text-gray-500 mt-2" x-show="role === 'super_admin'">
                    ⚠️ Super Admin has full access to all stores and settings.
                </p>
            </div>

            <!-- Показываем выбор магазина ТОЛЬКО если роль Менеджер -->
            <div class="mb-6" x-show="role === 'manager'" x-transition>
                <label class="block text-sm font-bold mb-2 text-blue-800">Assign to Store</label>
                <select name="tenant_id" class="w-full border-2 border-blue-200 p-2 rounded bg-blue-50">
                    <option value="" disabled selected>-- Select a Store --</option>
                    @foreach($tenants as $id => $data)
                        <option value="{{ $id }}">{{ $data['name'] }} ({{ $data['domain'] }})</option>
                    @endforeach
                </select>
                <p class="text-xs text-blue-600 mt-1">
                    This user will only manage products and orders for the selected store.
                </p>
            </div>

            <div class="flex justify-between items-center mt-6">
                <a href="{{ route('admin.users.index') }}" class="text-gray-500 py-2 hover:underline">Cancel</a>
                <button class="bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-500 shadow">
                    Create User
                </button>
            </div>
        </form>
    </div>
@endsection