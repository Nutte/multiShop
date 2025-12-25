<!-- FILE: resources/views/tenants/street_style/profile/order.blade.php -->
@extends('layouts.app')
@section('title', 'Receipt #' . $order->order_number)
@section('body_class', 'bg-black text-white font-sans')
@section('nav_class', 'bg-black border-b border-yellow-400')
@section('brand_name', 'STREET STYLE')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">

    <div class="border border-stone-700 bg-stone-800/50 p-6 relative">
        <!-- Watermark -->
        <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none overflow-hidden">
            <span class="text-9xl font-black text-white transform -rotate-12 whitespace-nowrap">OFFICIAL RECORD</span>
        </div>

        <div class="relative z-10">
            <!-- Header -->
            <div class="flex justify-between items-start border-b border-stone-700 pb-6 mb-6">
                <div>
                    <div class="text-xs text-stone-500 uppercase tracking-widest mb-1">TRANSACTION ID</div>
                    <div class="text-2xl font-bold text-white">{{ $order->order_number }}</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-stone-500 uppercase tracking-widest mb-1">STATUS</div>
                    <div class="inline-block px-2 py-1 bg-stone-700 text-orange-400 text-xs font-bold">{{ strtoupper($order->status) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Left Column: Items -->
                <div>
                    <h3 class="text-orange-600 text-xs font-bold mb-2 uppercase">SUPPLY LIST</h3>
                    <table class="w-full text-sm text-left">
                        <thead class="text-stone-500 text-xs border-b border-stone-700">
                            <tr>
                                <th class="py-2">ITEM</th>
                                <th class="py-2 text-center">QTY</th>
                                <th class="py-2 text-right">COST</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-700">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="py-3">
                                        <span class="block text-white font-bold">{{ $item->product_name }}</span>
                                        <span class="text-xs text-stone-500">{{ $item->sku }} | {{ $item->size }}</span>
                                    </td>
                                    <td class="py-3 text-center text-stone-400">{{ $item->quantity }}</td>
                                    <td class="py-3 text-right text-stone-300">${{ $item->total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Right Column: Info & Credentials -->
                <div class="space-y-6">
                    <div class="bg-stone-900 p-4 border border-stone-700 h-fit">
                        <h3 class="text-orange-600 text-xs font-bold mb-2 uppercase">LOGISTICS DATA</h3>
                        <div class="space-y-2 text-xs text-stone-400">
                            <div class="flex justify-between">
                                <span>RECIPIENT:</span>
                                <span class="text-white">{{ $order->customer_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>CONTACT:</span>
                                <span class="text-white">{{ $order->customer_phone }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>METHOD:</span>
                                <span class="text-white uppercase">{{ $order->shipping_method }}</span>
                            </div>
                            <div class="border-t border-stone-700 my-2 pt-2">
                                 <div class="block mb-1">COORDINATES (ADDRESS):</div>
                                 <div class="text-white bg-stone-800 p-2">{{ $order->shipping_address }}</div>
                            </div>
                        </div>

                        <div class="mt-6 border-t border-stone-700 pt-4 flex justify-between items-center">
                            <span class="text-stone-500 text-xs font-bold">TOTAL VALUE</span>
                            <span class="text-xl text-green-500 font-bold font-mono">${{ $order->total_amount }}</span>
                        </div>
                    </div>

                    <!-- CREDENTIALS BLOCK (Всегда виден) -->
                    <div class="border border-stone-600 p-4 bg-black/40">
                        <h4 class="font-bold text-white mb-2 uppercase border-b border-stone-700 pb-1">Access Credentials</h4>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="block text-[10px] text-stone-500 uppercase">LOGIN ID</span>
                                <span class="text-white font-mono text-sm">{{ $order->customer_phone }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-stone-500 uppercase">PASSWORD</span>
                                @if(isset($generatedPassword) && $generatedPassword)
                                    <!-- Если это новый заказ - показываем пароль -->
                                    <span class="text-orange-500 font-bold font-mono text-lg tracking-widest">{{ $generatedPassword }}</span>
                                @else
                                    <!-- Если старый - показываем маску -->
                                    <span class="text-stone-600 font-mono text-lg tracking-widest">********</span>
                                @endif
                            </div>
                        </div>
                        
                        @if(isset($generatedPassword) && $generatedPassword)
                            <div class="mt-2 text-[10px] text-orange-400">
                                ⚠ SAVE THIS KEY. IT WILL BE ENCRYPTED AFTER SESSION ENDS.
                            </div>
                        @else
                            <div class="mt-2 text-[10px] text-stone-500">
                                KEY IS ENCRYPTED. USE YOUR SAVED PASSWORD TO LOGIN.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('client.profile') }}" class="text-xs text-orange-500 hover:text-white transition uppercase font-bold">[ RETURN TO BASE ]</a>
            </div>
        </div>
    </div>
</div>
@endsection