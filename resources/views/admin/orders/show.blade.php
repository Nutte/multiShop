<!-- FILE: resources/views/admin/orders/show.blade.php -->
@extends('layouts.admin')
@section('title', 'Order Details')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.orders.index', ['tenant_id' => $currentTenantId]) }}" class="text-blue-600 hover:underline font-bold">&larr; Back to List</a>
        
        <div class="flex gap-2">
            <a href="{{ route('admin.orders.edit', [$order->id, 'tenant_id' => $currentTenantId]) }}" class="bg-blue-600 text-white px-4 py-2 rounded font-bold hover:bg-blue-500">
                Edit Order
            </a>
            <!-- Печать чека (открывает клиентский вид чека в новом окне, если нужно) -->
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- LEFT: INFO -->
        <div class="lg:col-span-1 space-y-6">
            <!-- STATUS CARD -->
            <div class="bg-white p-6 rounded shadow border-t-4 {{ $order->status == 'completed' ? 'border-green-500' : 'border-blue-500' }}">
                <h2 class="font-bold text-gray-700 mb-4 uppercase text-sm">Order Status</h2>
                
                <!-- ФОРМА ОБНОВЛЕНИЯ СТАТУСА (Fix: status_only mode) -->
                <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">
                    <!-- ВАЖНО: Этот флаг говорит контроллеру валидировать только статус -->
                    <input type="hidden" name="update_mode" value="status_only">

                    <div class="flex items-center gap-2">
                        <select name="status" class="flex-1 border p-2 rounded font-bold">
                            @foreach(['new', 'processing', 'shipped', 'completed', 'cancelled'] as $st)
                                <option value="{{ $st }}" {{ $order->status == $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-gray-50 p-3 rounded border">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <!-- Hidden input для отправки 0, если чекбокс снят -->
                            <input type="hidden" name="is_instagram" value="0">
                            <input type="checkbox" name="is_instagram" value="1" {{ $order->is_instagram ? 'checked' : '' }} class="text-pink-600 w-4 h-4">
                            <span class="text-sm font-bold text-pink-600">Is Instagram Order</span>
                        </label>
                    </div>

                    <button class="w-full bg-gray-800 text-white py-2 rounded font-bold hover:bg-gray-700">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- CUSTOMER CARD -->
            <div class="bg-white p-6 rounded shadow">
                <h2 class="font-bold text-gray-700 mb-4 uppercase text-sm border-b pb-2">Customer Info</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="block text-xs text-gray-400 uppercase">Name</span>
                        <span class="font-bold">{{ $order->customer_name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 uppercase">Phone</span>
                        <span class="font-mono bg-gray-100 px-2 py-1 rounded inline-block">{{ $order->customer_phone }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 uppercase">Email</span>
                        <span class="text-gray-600">{{ $order->customer_email ?? '-' }}</span>
                    </div>
                    @if($order->user_id)
                        <div class="pt-2 border-t">
                            <a href="{{ route('admin.users.show', ['user' => $order->user_id, 'tenant_id' => $currentTenantId]) }}" class="text-blue-600 hover:underline text-xs font-bold">
                                View User Profile &rarr;
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- SHIPPING CARD -->
            <div class="bg-white p-6 rounded shadow">
                <h2 class="font-bold text-gray-700 mb-4 uppercase text-sm border-b pb-2">Shipping Details</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="block text-xs text-gray-400 uppercase">Method</span>
                        <span class="font-bold uppercase">{{ $order->shipping_method }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-400 uppercase">Address</span>
                        <span class="text-gray-700">{{ $order->shipping_address }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: ITEMS -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded shadow overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                    <h2 class="font-bold text-gray-700 uppercase text-sm">Order Items</h2>
                    <span class="bg-gray-200 text-xs px-2 py-1 rounded font-bold text-gray-600">{{ $order->items->count() }} Items</span>
                </div>
                
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="p-4 text-xs font-bold text-gray-500 uppercase">Product</th>
                            <th class="p-4 text-xs font-bold text-gray-500 uppercase">SKU</th>
                            <th class="p-4 text-xs font-bold text-gray-500 uppercase text-center">Size</th>
                            <th class="p-4 text-xs font-bold text-gray-500 uppercase text-center">Qty</th>
                            <th class="p-4 text-xs font-bold text-gray-500 uppercase text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="p-4 font-bold text-gray-800">{{ $item->product_name }}</td>
                                <td class="p-4 font-mono text-gray-500 text-xs">{{ $item->sku }}</td>
                                <td class="p-4 text-center">
                                    <span class="bg-gray-100 px-2 py-1 rounded text-xs border">{{ $item->size }}</span>
                                </td>
                                <td class="p-4 text-center font-bold">{{ $item->quantity }}</td>
                                <td class="p-4 text-right font-mono">${{ $item->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                        <tr>
                            <td colspan="4" class="p-4 text-right text-xs font-bold text-gray-500 uppercase">Subtotal</td>
                            <td class="p-4 text-right font-mono font-bold">${{ $order->subtotal }}</td>
                        </tr>
                        @if($order->discount_amount > 0)
                            <tr>
                                <td colspan="4" class="p-4 text-right text-xs font-bold text-red-500 uppercase">Discount</td>
                                <td class="p-4 text-right font-mono font-bold text-red-500">-${{ $order->discount_amount }}</td>
                            </tr>
                        @endif
                        <tr class="text-lg">
                            <td colspan="4" class="p-4 text-right font-black uppercase">Total</td>
                            <td class="p-4 text-right font-mono font-black text-blue-600">${{ $order->total_amount }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection