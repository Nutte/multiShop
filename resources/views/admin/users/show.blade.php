<!-- FILE: resources/views/admin/users/show.blade.php -->
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
            <!-- Info Card -->
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
                        <div class="text-gray-800">{{ $user->email }}</div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 uppercase">Registration Date</label>
                        <div class="text-sm">{{ $user->created_at->format('d F Y, H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Security Card -->
            <div class="bg-white p-6 rounded shadow border-t-4 border-red-500">
                <h2 class="font-bold text-lg mb-4 text-gray-700 uppercase tracking-wide">Security & Access</h2>
                
                @if(session('success') && str_contains(session('success'), 'New Key'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                        <p class="font-bold">Password Reset Successful!</p>
                        <p class="text-sm mb-1">Please give this key to the customer:</p>
                        <div class="text-2xl font-mono font-black bg-white p-2 mt-1 select-all border border-green-300">
                            {{ explode(': ', session('success'))[1] }}
                        </div>
                    </div>
                @endif

                <p class="text-sm text-gray-600 mb-4">
                    If the customer forgot their password, you can generate a new Access Key for them.
                </p>

                <form action="{{ route('admin.users.update', ['user' => $user->id, 'tenant_id' => request('tenant_id')]) }}" method="POST" onsubmit="return confirm('Are you sure? This will overwrite the user\'s current password.');">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="generate_password" value="1">
                    <button class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded transition flex items-center justify-center gap-2">
                        <span>🔒 Reset Password / Generate Key</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- RIGHT: ORDERS HISTORY -->
        <div class="col-span-2">
            <div class="bg-white rounded shadow overflow-hidden">
                <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                    <h2 class="font-bold text-lg text-gray-700 uppercase tracking-wide">Order History</h2>
                    <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs font-bold">{{ $user->orders->count() }} Orders</span>
                </div>

                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b">
                        <tr>
                            <th class="px-6 py-3">Order #</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Total</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($user->orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-mono font-bold text-blue-600">
                                    {{ $order->order_number }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    {{ $order->created_at->format('d M Y') }}
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
                                    @endphp
                                    <span class="px-2 py-1 rounded text-xs font-bold uppercase {{ $colors[$order->status] ?? 'bg-gray-100' }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-bold">
                                    ${{ $order->total_amount }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', ['order' => $order->id, 'tenant_id' => request('tenant_id')]) }}" class="text-gray-500 hover:text-black font-bold text-sm">
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
@endsection