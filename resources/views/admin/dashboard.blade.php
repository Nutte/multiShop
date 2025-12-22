<!-- FILE: resources/views/admin/dashboard.blade.php -->
@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Overview</h1>
        
        @if($isSuperAdmin)
            <div class="bg-yellow-100 border border-yellow-300 p-2 rounded">
                <span class="text-xs font-bold text-yellow-800 block mb-1">SUPER ADMIN CONTROLS</span>
                <div class="flex gap-2">
                    <a href="http://street.trishop.local/admin" class="text-blue-600 text-sm hover:underline">Switch to Street</a> |
                    <a href="http://designer.trishop.local/admin" class="text-blue-600 text-sm hover:underline">Switch to Designer</a> |
                    <a href="http://military.trishop.local/admin" class="text-blue-600 text-sm hover:underline">Switch to Military</a>
                </div>
            </div>
        @endif
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded shadow border-l-4 border-blue-500">
            <div class="text-gray-500 text-sm uppercase font-bold">Total Orders</div>
            <div class="text-3xl font-bold">{{ $totalOrders }}</div>
        </div>
        <div class="bg-white p-6 rounded shadow border-l-4 border-green-500">
            <div class="text-gray-500 text-sm uppercase font-bold">Revenue</div>
            <div class="text-3xl font-bold">${{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="bg-white p-6 rounded shadow border-l-4 border-purple-500">
            <div class="text-gray-500 text-sm uppercase font-bold">Tenant</div>
            <div class="text-xl font-bold capitalize">{{ str_replace('_', ' ', $currentTenant) }}</div>
        </div>
    </div>

    <!-- Recent Orders -->
    <h2 class="text-xl font-bold mb-4">Recent Orders</h2>
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($recentOrders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-mono">{{ $order->order_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $order->customer_email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold">${{ $order->total_amount }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection