<!-- FILE: resources/views/admin/orders/show.blade.php -->
@extends('layouts.admin')
@section('title', 'Order ' . $order->order_number)

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold flex items-center gap-2">
            <a href="{{ route('admin.orders.index', ['tenant_id' => request('tenant_id')]) }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            Order #{{ $order->order_number }}
        </h1>
        <div class="text-sm text-gray-500">
            Placed on {{ $order->created_at->format('F d, Y H:i') }}
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- LEFT: Items -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded shadow overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="p-4 text-xs font-bold uppercase text-gray-500">Product</th>
                            <th class="p-4 text-xs font-bold uppercase text-gray-500 text-right">Price</th>
                            <th class="p-4 text-xs font-bold uppercase text-gray-500 text-center">Qty</th>
                            <th class="p-4 text-xs font-bold uppercase text-gray-500 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr class="border-b last:border-0">
                                <td class="p-4">
                                    <div class="font-bold">{{ $item->product_name }}</div>
                                    <div class="text-xs text-gray-500">SKU: {{ $item->sku }} | Size: {{ $item->size ?? 'N/A' }}</div>
                                </td>
                                <td class="p-4 text-right">${{ $item->price }}</td>
                                <td class="p-4 text-center">{{ $item->quantity }}</td>
                                <td class="p-4 text-right font-bold">${{ $item->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="p-4 text-right font-bold">Subtotal:</td>
                            <td class="p-4 text-right">${{ $order->subtotal }}</td>
                        </tr>
                        @if($order->discount_amount > 0)
                            <tr>
                                <td colspan="3" class="p-4 text-right font-bold text-green-600">Discount ({{ $order->promo_code }}):</td>
                                <td class="p-4 text-right text-green-600">-${{ $order->discount_amount }}</td>
                            </tr>
                        @endif
                        <tr class="text-lg">
                            <td colspan="3" class="p-4 text-right font-bold">Total:</td>
                            <td class="p-4 text-right font-bold">${{ $order->total_amount }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- RIGHT: Info & Actions -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Status Manager -->
            <div class="bg-white p-6 rounded shadow border-l-4 border-blue-600">
                <h3 class="font-bold text-gray-700 mb-4">Update Status</h3>
                <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @if(request('tenant_id')) <input type="hidden" name="tenant_id" value="{{ request('tenant_id') }}"> @endif
                    
                    <select name="status" class="w-full border p-2 rounded mb-4 bg-gray-50">
                        @foreach(['new', 'processing', 'shipped', 'completed', 'cancelled'] as $st)
                            <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>
                                {{ ucfirst($st) }}
                            </option>
                        @endforeach
                    </select>
                    <button class="w-full bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-500">Update Status</button>
                </form>
            </div>

            <!-- Customer Info -->
            <div class="bg-white p-6 rounded shadow">
                <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Customer Details</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-gray-500 block text-xs">Name</span>
                        <span class="font-bold">{{ $order->customer_name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Phone</span>
                        <a href="tel:{{ $order->customer_phone }}" class="text-blue-600">{{ $order->customer_phone }}</a>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Email</span>
                        <a href="mailto:{{ $order->customer_email }}" class="text-blue-600">{{ $order->customer_email }}</a>
                    </div>
                </div>
            </div>

            <!-- Shipping Info -->
            <div class="bg-white p-6 rounded shadow">
                <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Shipping & Payment</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-gray-500 block text-xs">Method</span>
                        <span class="font-bold uppercase">{{ str_replace('_', ' ', $order->shipping_method) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Address</span>
                        <div class="bg-gray-50 p-2 rounded border">{{ $order->shipping_address }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Payment</span>
                        <span class="font-bold uppercase badge bg-yellow-100 text-yellow-800 px-2 rounded">
                            {{ $order->payment_method }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection