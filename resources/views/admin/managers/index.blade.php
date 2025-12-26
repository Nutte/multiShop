<!-- FILE: resources/views/admin/managers/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Managers')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Store Managers</h1>
        <a href="{{ route('admin.managers.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded font-bold hover:bg-blue-500 shadow">
            + Add Manager
        </a>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-4 text-sm font-bold text-gray-600">Name</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Contact</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Assigned Store</th>
                    <th class="p-4 text-sm font-bold text-gray-600 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($managers as $manager)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4 font-bold">{{ $manager->name }}</td>
                        <td class="p-4">
                            <div class="text-sm">{{ $manager->email }}</div>
                            <div class="text-xs text-gray-500">{{ $manager->phone }}</div>
                        </td>
                        <td class="p-4">
                            @php
                                $tenantName = config("tenants.tenants.{$manager->tenant_id}.name", 'Unknown Store');
                            @endphp
                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-bold uppercase border border-yellow-200">
                                {{ $tenantName }}
                            </span>
                            <div class="text-[10px] text-gray-400 mt-1">{{ $manager->tenant_id }}</div>
                        </td>
                        <td class="p-4 text-right flex justify-end gap-2">
                            <a href="{{ route('admin.managers.edit', $manager->id) }}" class="text-blue-600 hover:text-blue-800 font-bold text-sm">Edit</a>
                            
                            <form action="{{ route('admin.managers.destroy', $manager->id) }}" method="POST" onsubmit="return confirm('Delete this manager?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-500 hover:text-red-700 font-bold text-sm ml-2">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-400 italic">No managers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection