@extends('tenants.military_gear.layouts.military')
@section('title', 'Invoice #' . $order->order_number)

@section('content')
<div id="invoice-page" class="min-h-screen bg-white text-black p-8 md:p-16 relative">
    <!-- Back Button (Screen Only) -->
    <a href="{{ route('client.profile') }}" class="absolute top-8 left-8 no-print px-4 py-2 bg-black text-white font-mono text-xs uppercase hover:bg-gray-800">
        <- Return to Base
    </a>
    <button onclick="window.print()" class="absolute top-8 right-8 no-print px-4 py-2 bg-military-accent text-black font-bold font-mono text-xs uppercase hover:bg-orange-500">
        [ Print Document ]
    </button>

    <!-- Invoice Content -->
    <div class="max-w-4xl mx-auto border-2 border-black p-8 relative">
        <!-- Watermark -->
        <div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none overflow-hidden">
            <h1 class="text-[150px] font-bold uppercase rotate-45 whitespace-nowrap">OFFICIAL</h1>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-start mb-12 border-b-2 border-black pb-8">
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 bg-black text-white flex items-center justify-center font-bold text-xl">K</div>
                    <h1 class="text-4xl font-bold uppercase tracking-widest">Karakurt.UA</h1>
                </div>
                <p class="font-mono text-sm uppercase">
                    Reitarska St, 21/13, Kyiv, UA<br>
                    TAX ID: 445-992-001<br>
                    SUPPLY@KARAKURT.UA
                </p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold uppercase mb-2">Invoice</h2>
                <p class="font-mono text-sm">NO: #{{ $order->order_number }}</p>
                <p class="font-mono text-sm">DATE: {{ $order->created_at->format('Y-m-d') }}</p>
            </div>
        </div>

        <!-- Bill To -->
        <div class="mb-12 flex justify-between">
            <div>
                <h3 class="font-bold uppercase text-sm mb-2 border-b border-black inline-block">Billed To:</h3>
                <p class="font-mono text-sm mt-2 uppercase">
                    {{ $order->customer_name }}<br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->customer_phone }}
                </p>
            </div>
            <div class="text-right">
                <h3 class="font-bold uppercase text-sm mb-2 border-b border-black inline-block">Payment Method:</h3>
                <p class="font-mono text-sm mt-2 uppercase">
                    {{ $order->payment_method }}<br>
                    AUTH CODE: {{ substr(md5($order->id), 0, 6) }}
                </p>
            </div>
        </div>

        <!-- Table -->
        <table class="w-full font-mono text-sm mb-12">
            <thead>
                <tr class="border-b-2 border-black">
                    <th class="text-left py-2 uppercase">Item / Description</th>
                    <th class="text-center py-2 uppercase">Qty</th>
                    <th class="text-right py-2 uppercase">Unit Price</th>
                    <th class="text-right py-2 uppercase">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr class="border-b border-gray-300">
                    <td class="py-4">
                        <span class="font-bold block">{{ $item->product_name }}</span>
                        <span class="text-xs text-gray-600">SKU: {{ $item->sku }} / SIZE: {{ $item->size }}</span>
                    </td>
                    <td class="text-center py-4">{{ $item->quantity }}</td>
                    <td class="text-right py-4">{{ number_format($item->price) }} ₴</td>
                    <td class="text-right py-4 font-bold">{{ number_format($item->total) }} ₴</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end mb-12">
            <div class="w-64 space-y-2 font-mono text-sm">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span>{{ number_format($order->subtotal) }} ₴</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-gray-600">
                    <span>Discount:</span>
                    <span>-{{ number_format($order->discount_amount) }} ₴</span>
                </div>
                @endif
                <div class="flex justify-between border-t-2 border-black pt-2 text-lg font-bold">
                    <span>Total:</span>
                    <span>{{ number_format($order->total_amount) }} ₴</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-20 pt-8 border-t-2 border-black flex justify-between items-end">
            <div class="font-mono text-xs max-w-sm">
                <p class="uppercase font-bold mb-1">Terms & Conditions:</p>
                <p>No refunds on tactical gear after 14 days. Wash cold, hang dry. Thank you for your service.</p>
            </div>
            <div class="text-center">
                <div class="h-12 w-48 mb-2 border-b border-black"></div>
                <p class="font-mono text-xs uppercase font-bold">Authorized Signature</p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; padding: 0 !important; margin: 0 !important; }
        body * { visibility: hidden; }
        #invoice-page, #invoice-page * { visibility: visible; }
        #invoice-page {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none !important;
            border: none !important;
            background: white !important;
            color: black !important;
            transform: none !important; 
        }
        #invoice-page * { color: black !important; border-color: #ddd !important; }
        #invoice-page h1 { font-size: 24pt !important; }
    }
</style>
@endsection