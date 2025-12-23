<!-- FILE: resources/views/admin/users/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Users Management')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Users & Roles</h1>
            <p class="text-sm text-gray-500">Manage access to the platform.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500 shadow-sm flex items-center gap-2">
            <span>+</span> Create User
        </a>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Store</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold mr-3">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->role === 'super_admin')
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800 border border-purple-200">
                                    Super Admin
                                </span>
                            @else
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                    Manager
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->role === 'super_admin')
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Global Access</span>
                            @elseif($user->tenant_id && isset($tenants[$user->tenant_id]))
                                <div class="flex items-center gap-2">
                                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                    <span class="text-sm font-bold text-gray-700">{{ $tenants[$user->tenant_id]['name'] }}</span>
                                </div>
                                <div class="text-xs text-gray-400 pl-4">{{ $user->tenant_id }}</div>
                            @else
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded bg-red-100 text-red-600">
                                    Unassigned / Invalid
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection