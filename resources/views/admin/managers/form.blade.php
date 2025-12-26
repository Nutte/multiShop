<!-- FILE: resources/views/admin/managers/form.blade.php -->
@extends('layouts.admin')

@php
    $isEdit = isset($manager);
    $title = $isEdit ? 'Edit Manager: ' . $manager->name : 'New Manager';
    $action = $isEdit ? route('admin.managers.update', $manager->id) : route('admin.managers.store');
@endphp

@section('title', $title)

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">{{ $title }}</h1>
            <a href="{{ route('admin.managers.index') }}" class="text-gray-500 hover:underline">Back to List</a>
        </div>

        <div class="bg-white p-6 rounded shadow border-t-4 {{ $isEdit ? 'border-blue-600' : 'border-green-600' }}">
            <form action="{{ $action }}" method="POST" class="space-y-6">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif
                
                <!-- Name -->
                <div>
                    <label class="block text-sm font-bold mb-1 text-gray-700">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $manager->name ?? '') }}" class="w-full border p-2 rounded focus:border-blue-500 outline-none transition" required placeholder="John Doe">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Contact Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold mb-1 text-gray-700">Email</label>
                        <input type="email" name="email" value="{{ old('email', $manager->email ?? '') }}" class="w-full border p-2 rounded focus:border-blue-500 outline-none transition" required placeholder="manager@example.com">
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-1 text-gray-700">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $manager->phone ?? '') }}" class="w-full border p-2 rounded focus:border-blue-500 outline-none transition" required placeholder="+380...">
                        @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Password -->
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <label class="block text-sm font-bold mb-1 text-gray-700">
                        {{ $isEdit ? 'New Password (Optional)' : 'Password' }}
                    </label>
                    <input type="password" name="password" class="w-full border p-2 rounded focus:border-blue-500 outline-none transition" {{ $isEdit ? '' : 'required' }} placeholder="{{ $isEdit ? 'Leave empty to keep current' : '********' }}">
                    @if($isEdit)
                        <p class="text-xs text-gray-400 mt-1">Only fill this if you want to change the manager's password.</p>
                    @endif
                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Store Assignment -->
                <div class="bg-yellow-50 p-4 rounded border border-yellow-200">
                    <label class="block text-sm font-bold mb-1 text-yellow-800">Assign to Store</label>
                    <select name="tenant_id" class="w-full border p-2 rounded bg-white focus:border-yellow-500 outline-none" required>
                        <option value="">-- Select Store --</option>
                        @foreach($tenants as $id => $data)
                            <option value="{{ $id }}" {{ (old('tenant_id', $manager->tenant_id ?? '') == $id) ? 'selected' : '' }}>
                                {{ $data['name'] }} ({{ $id }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <span class="font-bold text-yellow-700">Warning:</span> This manager will ONLY see orders and products of the selected store.
                    </p>
                    @error('tenant_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Submit Button -->
                <div class="pt-4 flex items-center justify-end border-t mt-6">
                    <button class="{{ $isEdit ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-8 py-3 rounded font-bold transition shadow-lg flex items-center gap-2">
                        @if($isEdit)
                            <span>💾 Update Manager</span>
                        @else
                            <span>➕ Create Manager</span>
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection