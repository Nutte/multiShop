<!-- FILE: resources/views/admin/dashboard.blade.php -->
@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-gray-500">Overview for: <span class="font-bold text-blue-600">{{ $stats['tenant_name'] }}</span></p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-6 rounded shadow border-l-4 border-blue-500">
            <div class="text-gray-500 text-sm font-bold uppercase mb-1">Total Orders</div>
            <div class="text-3xl font-bold">{{ number_format($stats['total_orders']) }}</div>
        </div>
        <div class="bg-white p-6 rounded shadow border-l-4 border-green-500">
            <div class="text-gray-500 text-sm font-bold uppercase mb-1">Total Revenue</div>
            <div class="text-3xl font-bold">${{ number_format($stats['total_revenue'], 2) }}</div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="bg-white rounded shadow overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
            <h3 class="font-bold text-gray-700">Recent Orders</h3>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-blue-600 hover:underline">View All</a>
        </div>
        <table class="w-full text-left">
            <thead>
                <tr class="text-xs text-gray-500 uppercase border-b">
                    <th class="px-6 py-3">Order #</th>
                    @if(!$currentTenantId)<th class="px-6 py-3">Store</th>@endif
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3">Total</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @forelse($stats['recent_orders'] as $order)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono font-bold">{{ $order->order_number }}</td>
                        @if(!$currentTenantId)
                            <td class="px-6 py-4 text-blue-600 font-bold text-xs">{{ $order->store_name ?? '-' }}</td>
                        @endif
                        <td class="px-6 py-4">{{ $order->customer_name }}</td>
                        <td class="px-6 py-4 font-bold">${{ $order->total_amount }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-600">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                             <a href="{{ route('admin.orders.show', [$order->id, 'tenant_id' => $order->tenant_id ?? $currentTenantId]) }}" class="text-blue-600 hover:underline font-bold">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400 italic">No orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection