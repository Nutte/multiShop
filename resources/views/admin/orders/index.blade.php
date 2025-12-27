<!-- FILE: resources/views/admin/orders/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Orders')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h1 class="text-2xl font-bold flex items-center gap-2">
            <span>📦 Orders</span>
            @if(auth()->user()->role === 'super_admin' && !$currentTenantId)
                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">All Stores</span>
            @endif
        </h1>
        
        <div class="flex gap-4">
            <!-- КНОПКА СОЗДАНИЯ ЗАКАЗА -->
            <a href="{{ route('admin.orders.create', ['tenant_id' => $currentTenantId]) }}" class="bg-green-600 text-white px-4 py-2 rounded font-bold hover:bg-green-500 shadow flex items-center gap-2">
                <span>+ Manual Order</span>
            </a>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="bg-white p-4 rounded shadow mb-6 border border-gray-200">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <!-- Store Selector (Super Admin Only) -->
            @if(auth()->user()->role === 'super_admin')
                <div class="w-full md:w-64">
                    <label class="block text-xs font-bold text-gray-500 mb-1">Store Context</label>
                    <select name="tenant_id" class="w-full border p-2 rounded bg-yellow-50 border-yellow-200" onchange="this.form.submit()">
                        <option value="">ALL STORES (Overview)</option>
                        @foreach(config('tenants.tenants') as $id => $data)
                            <option value="{{ $id }}" {{ $currentTenantId === $id ? 'selected' : '' }}>
                                {{ $data['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="text-xs text-gray-500 pb-2">
                Showing latest orders.
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-4 text-sm font-bold text-gray-600">Order #</th>
                    <!-- Колонка магазина, если смотрим все -->
                    @if(auth()->user()->role === 'super_admin' && !$currentTenantId)
                        <th class="p-4 text-sm font-bold text-gray-600">Store</th>
                    @endif
                    <th class="p-4 text-sm font-bold text-gray-600">Date</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Customer</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Status</th>
                    <th class="p-4 text-sm font-bold text-gray-600 text-right">Total</th>
                    <th class="p-4 text-sm font-bold text-gray-600 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b hover:bg-gray-50 {{ $order->is_instagram ? 'bg-pink-50' : '' }}">
                        <td class="p-4 font-mono font-bold text-blue-600">
                            {{ $order->order_number }}
                            @if($order->is_instagram)
                                <span class="text-[10px] block text-pink-600">Instagram</span>
                            @endif
                        </td>
                        
                        @if(auth()->user()->role === 'super_admin' && !$currentTenantId)
                            <td class="p-4">
                                <span class="text-[10px] uppercase font-bold bg-gray-200 px-2 py-1 rounded text-gray-600">
                                    {{ $order->tenant_name ?? $order->tenant_id }}
                                </span>
                            </td>
                        @endif

                        <td class="p-4 text-sm text-gray-500">
                            {{ $order->created_at->format('d M Y') }}<br>
                            <span class="text-xs">{{ $order->created_at->format('H:i') }}</span>
                        </td>
                        <td class="p-4">
                            <div class="font-bold">{{ $order->customer_name }}</div>
                            <div class="text-xs text-gray-500">{{ $order->customer_phone }}</div>
                        </td>
                        <td class="p-4">
                            <!-- Используем хелпер цветов -->
                            <span class="px-2 py-1 rounded text-xs font-bold uppercase {{ $order->status_color }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="p-4 text-right font-bold text-gray-800">
                            ${{ $order->total_amount }}
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.orders.show', [$order->id, 'tenant_id' => $order->tenant_id ?? $currentTenantId]) }}" class="bg-gray-100 text-gray-600 px-3 py-1 rounded text-xs font-bold hover:bg-gray-200">
                                    View
                                </a>
                                <!-- КНОПКА РЕДАКТИРОВАНИЯ -->
                                <a href="{{ route('admin.orders.edit', [$order->id, 'tenant_id' => $order->tenant_id ?? $currentTenantId]) }}" class="bg-blue-600 text-white px-3 py-1 rounded text-xs font-bold hover:bg-blue-500">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-400 italic">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="p-4 border-t">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    </div>
@endsection