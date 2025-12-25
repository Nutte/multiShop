<!-- FILE: resources/views/admin/orders/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Orders')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Orders Management</h1>
    </div>

    <!-- FILTERS -->
    <div class="bg-white p-4 rounded shadow mb-6 border border-gray-200">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex gap-4 items-end">
            @if(auth()->user()->role === 'super_admin')
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Store Context</label>
                    <select name="tenant_id" class="border p-2 rounded bg-yellow-50 font-bold text-gray-800" onchange="this.form.submit()">
                        <!-- ИСПРАВЛЕНИЕ: Четкое название для глобального режима -->
                        <option value="" {{ empty($currentTenantId) ? 'selected' : '' }}>🌎 All Shops</option>
                        
                        @foreach(config('tenants.tenants') as $id => $data)
                            <option value="{{ $id }}" {{ $currentTenantId == $id ? 'selected' : '' }}>
                                🏪 {{ $data['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Order # or Phone" class="border p-2 rounded">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1">Status</label>
                <select name="status" class="border p-2 rounded">
                    <option value="">All Statuses</option>
                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <button class="bg-blue-600 text-white px-4 py-2 rounded font-bold hover:bg-blue-500">Filter</button>
        </form>
    </div>

    <!-- LIST -->
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-4 font-bold text-gray-600">Order #</th>
                    <!-- Показываем колонку Store только в глобальном режиме -->
                    @if(auth()->user()->role === 'super_admin' && !$currentTenantId)
                        <th class="p-4 font-bold text-gray-600">Store</th>
                    @endif
                    <th class="p-4 font-bold text-gray-600">Customer</th>
                    <th class="p-4 font-bold text-gray-600">Total</th>
                    <th class="p-4 font-bold text-gray-600">Status</th>
                    <th class="p-4 font-bold text-gray-600">Date</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-4 font-mono font-bold">{{ $order->order_number }}</td>
                        
                        @if(auth()->user()->role === 'super_admin' && !$currentTenantId)
                            <td class="p-4 text-sm text-blue-600 font-bold">
                                {{ $order->tenant_name ?? 'Unknown' }}
                            </td>
                        @endif

                        <td class="p-4">
                            <div class="font-bold">{{ $order->customer_name }}</div>
                            <div class="text-xs text-gray-500">{{ $order->customer_phone }}</div>
                        </td>
                        <td class="p-4 font-bold">${{ $order->total_amount }}</td>
                        <td class="p-4">
                            @php
                                $colors = [
                                    'new' => 'bg-blue-100 text-blue-800',
                                    'processing' => 'bg-yellow-100 text-yellow-800',
                                    'shipped' => 'bg-purple-100 text-purple-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase {{ $colors[$order->status] ?? 'bg-gray-100' }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="p-4 text-sm text-gray-500">{{ $order->created_at->format('M d, H:i') }}</td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.orders.show', ['order' => $order->id, 'tenant_id' => $order->tenant_id ?? request('tenant_id')]) }}" class="bg-gray-800 text-white px-3 py-1 rounded text-sm font-bold hover:bg-gray-700">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ (auth()->user()->role === 'super_admin' && !$currentTenantId) ? 7 : 6 }}" class="p-8 text-center text-gray-400">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $orders->withQueryString()->links() }}</div>
    </div>
@endsection