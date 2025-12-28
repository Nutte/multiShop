<!-- FILE: resources/views/admin/users/form.blade.php -->
@extends('layouts.admin')

@php
    $isEdit = $user->exists;
    $title = $isEdit ? 'Edit Customer: ' . $user->name : 'Create New Customer';
    // Для экшена передаем только ID, так как контроллер переделан на прием ID
    $action = $isEdit ? route('admin.users.update', $user->id) : route('admin.users.store');
    $isSuperAdmin = auth()->user()->role === 'super_admin';
@endphp

@section('title', $title)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ $title }}</h1>
        <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:underline">Back to List</a>
    </div>

    <div class="bg-white rounded shadow-lg overflow-hidden border-t-4 border-blue-600">
        <form action="{{ $action }}" method="POST" class="p-6 space-y-6">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- БЛОК ПРИВЯЗКИ К МАГАЗИНУ (Только для Супер-Админа) -->
            @if($isSuperAdmin)
                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Assigned Store</label>
                    
                    @if($isEdit)
                        <!-- При редактировании менять нельзя -->
                        <div class="font-bold text-gray-800 flex items-center gap-2">
                            <span>🏢</span>
                            {{ $user->tenant_id ? (config("tenants.tenants.{$user->tenant_id}.name") ?? $user->tenant_id) : 'Global / No Store' }}
                            <span class="text-xs text-gray-400 font-normal ml-2">(Cannot be changed after creation)</span>
                        </div>
                    @else
                        <!-- При создании можно выбрать -->
                        <select name="tenant_id" class="w-full border p-2 rounded bg-white">
                            <option value="">-- Global User (No specific store) --</option>
                            @foreach($tenants as $id => $data)
                                <option value="{{ $id }}" {{ old('tenant_id') == $id ? 'selected' : '' }}>
                                    {{ $data['name'] }} ({{ $id }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Select which store this customer belongs to primarily.</p>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border p-2 rounded" required placeholder="John Doe">
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number (Login)</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full border p-2 rounded" required placeholder="097...">
                    <p class="text-xs text-gray-400 mt-1">Format: 0XX... or +380... (Will be auto-formatted to +380...)</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border p-2 rounded" placeholder="john@example.com">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">
                        {{ $isEdit ? 'New Password (Optional)' : 'Password' }}
                    </label>
                    <input type="text" name="password" class="w-full border p-2 rounded font-mono" placeholder="{{ $isEdit ? 'Leave blank to keep current' : 'Enter password' }}" {{ $isEdit ? '' : 'required' }}>
                    
                    @if(!$isEdit)
                        <button type="button" onclick="document.querySelector('[name=password]').value = Math.random().toString(36).slice(-8);" class="text-xs text-blue-600 hover:underline mt-1 cursor-pointer">
                            Generate Random
                        </button>
                    @endif
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="pt-6 border-t flex justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Cancel</a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-500 shadow">
                    {{ $isEdit ? 'Update Customer' : 'Create Customer' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection