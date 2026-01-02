@extends('layouts.admin')
@section('title', 'Customer Profile')

@section('content')

    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.users.index', ['tenant_id' => request('tenant_id')]) }}" class="text-blue-600 hover:underline font-bold">&larr; Back to List</a>
        <h1 class="text-2xl font-bold">Customer Profile #{{ $user->id }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- LEFT: INFO & ACTIONS -->
        <div class="col-span-1 space-y-6">
            <div class="bg-white p-6 rounded shadow border-t-4 border-blue-600">
                <h2 class="font-bold text-lg mb-4 text-gray-700 uppercase tracking-wide">Personal Data</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Full Name</label>
                        <div class="font-bold text-lg">{{ $user->name }}</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Phone (Login)</label>
                        <div class="font-mono font-bold text-lg bg-gray-100 p-2 rounded">{{ $user->phone }}</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Email</label>
                        <div class="text-gray-800">{{ $user->email ?? 'Not provided' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Registered In</label>
                        <span class="inline-block px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">
                             {{ $user->tenant_id ? config("tenants.tenants.{$user->tenant_id}.name", $user->tenant_id) : 'Global / All Stores' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h2 class="font-bold text-lg mb-4 text-gray-700 uppercase tracking-wide">Actions</h2>
                <div class="flex flex-col space-y-3">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="block w-full text-center bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition">
                        Edit Profile
                    </a>
                    
                    @if(auth()->user()->role === 'super_admin')
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="block w-full text-center bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition">
                            Delete Customer
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- RIGHT: ORDERS LIST -->
        <div class="col-span-1 md:col-span-2">
            <div class="bg-white rounded shadow overflow-hidden">
                <div class="bg-gray-5 p-4 border-b flex justify-between items-center">
                    <h2 class="font-bold text-lg text-gray-700">Order History</h2>
                    <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full">{{ $user->orders->count() }} Orders</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 text-sm uppercase tracking-wider">
                                <th class="px-6 py-3 font-medium">Order #</th>
                                <th class="px-6 py-3 font-medium">Store</th>
                                <th class="px-6 py-3 font-medium">Status</th>
                                <th class="px-6 py-3 font-medium">Total</th>
                                <th class="px-6 py-3 font-medium text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($user->orders as $order)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">
                                            #{{ $order->order_number }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $order->created_at->format('d M Y, H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $order->tenant_name ?? $order->tenant_id }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $colors = [
                                                'new' => 'bg-blue-100 text-blue-800',
                                                'processing' => 'bg-yellow-100 text-yellow-800',
                                                'shipped' => 'bg-purple-100 text-purple-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                            ];
                                            $statusColor = $colors[$order->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 rounded text-xs font-bold uppercase {{ $statusColor }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold">
                                        {{ number_format($order->total_amount, 0, '.', ' ') }} грн.
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        {{-- ИСПРАВЛЕНИЕ: Параметр 'id' вместо 'order' --}}
                                        <a href="{{ route('admin.orders.show', ['id' => $order->id, 'tenant_id' => $order->tenant_id ?? request('tenant_id')]) }}" class="text-blue-600 hover:text-blue-800 font-bold text-sm">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                                        No orders found for this customer.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection