<!-- FILE: resources/views/admin/promocodes/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Promo Codes')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Promo Codes Management</h1>
        <a href="{{ route('admin.promocodes.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded font-bold hover:bg-blue-500 shadow">
            + Create New Code
        </a>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-4 text-sm font-bold text-gray-600">Code</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Discount</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Scope</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Validity</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Status</th>
                    <th class="p-4 text-sm font-bold text-gray-600 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promocodes as $code)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4">
                            <span class="font-mono font-bold text-lg text-blue-600 bg-blue-50 px-2 py-1 rounded border border-blue-100">
                                {{ $code->code }}
                            </span>
                        </td>
                        <td class="p-4 font-bold">
                            {{ $code->type === 'percent' ? '-' . $code->value . '%' : '-$' . $code->value }}
                        </td>
                        <td class="p-4">
                            @if($code->scope_type === 'global')
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded font-bold">Global (All Stores)</span>
                            @elseif($code->scope_type === 'specific')
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded font-bold">Specific Products</span>
                            @elseif($code->scope_type === 'category')
                                <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded font-bold">Category</span>
                            @elseif($code->scope_type === 'line')
                                <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded font-bold">Collection</span>
                            @endif
                        </td>
                        <td class="p-4 text-sm text-gray-500">
                            @if($code->expires_at)
                                Allows until {{ $code->expires_at->format('M d, Y') }}
                            @else
                                <span class="text-green-600">Forever</span>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($code->isValid())
                                <span class="text-green-600 font-bold flex items-center gap-1">● Active</span>
                            @else
                                <span class="text-red-500 font-bold flex items-center gap-1">○ Inactive</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <form action="{{ route('admin.promocodes.destroy', $code->id) }}" method="POST" onsubmit="return confirm('Delete this promo code?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-500 hover:bg-red-50 p-2 rounded font-bold transition">&times; Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400 italic">No promo codes created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection