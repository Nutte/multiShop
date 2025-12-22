<!-- FILE: resources/views/admin/orders/show.blade.php -->
@extends('layouts.admin')
@section('title', 'Order ' . $order->order_number)

@section('content')
    <div class="flex justify-between items-start mb-6">
        <h1 class="text-2xl font-bold">Order: {{ $order->order_number }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="text-gray-500 hover:text-gray-700">Back to List</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded shadow p-6">
                <h2 class="font-bold text-lg mb-4">Items</h2>
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-gray-500 text-sm">
                            <th class="pb-2">Product</th>
                            <th class="pb-2">Qty</th>
                            <th class="pb-2 text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($order->items as $item)
                        <tr>
                            <td class="py-3">{{ $item['name'] }}</td>
                            <td class="py-3">{{ $item['quantity'] }}</td>
                            <td class="py-3 text-right">${{ $item['price'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t">
                        <tr>
                            <td colspan="2" class="pt-4 font-bold text-right">Total:</td>
                            <td class="pt-4 font-bold text-right text-lg">${{ $order->total_amount }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-6">
            <!-- Status Manager -->
            <div class="bg-white rounded shadow p-6">
                <h2 class="font-bold text-gray-700 mb-4">Actions</h2>
                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST" class="mb-4">
                    @csrf
                    <label class="block text-xs font-bold text-gray-500 mb-1">Update Status</label>
                    <div class="flex gap-2">
                        <select name="status" class="flex-1 border p-2 rounded text-sm">
                            <option value="new" {{ $order->status == 'new' ? 'selected' : '' }}>New</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <button class="bg-blue-600 text-white px-3 rounded text-sm font-bold">Save</button>
                    </div>
                </form>

                <hr class="my-4">

                <!-- Integrations -->
                <form action="{{ route('admin.orders.notify', $order->id) }}" method="POST">
                    @csrf
                    <button class="w-full bg-blue-500 text-white py-2 rounded mb-2 text-sm flex items-center justify-center gap-2 hover:bg-blue-400">
                        <span>✈️</span> Send Telegram Alert
                    </button>
                </form>
                
                @if($order->status === 'processing' || $order->status === 'paid')
                    <button disabled class="w-full bg-red-100 text-red-400 py-2 rounded text-sm border border-red-200 cursor-not-allowed">
                        Create Nova Poshta TTN (No API Key)
                    </button>
                @endif
            </div>
        </div>
    </div>
@endsection