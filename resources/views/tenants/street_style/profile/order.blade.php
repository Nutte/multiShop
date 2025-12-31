<!-- FILE: resources/views/tenants/street_style/profile/order.blade.php -->
@extends('tenants.street_style.layouts.artefact')
@section('title', 'Receipt #' . $order->order_number . ' - ARTEFACT.ROOM')

@section('content')
<div class="max-w-3xl mx-auto py-8">

    <!-- ACTION PANEL -->
    <div class="flex justify-between items-center mb-6 no-print">
        <a href="{{ route('client.profile') }}" class="text-white text-xs font-tech uppercase flex items-center gap-2 hover:text-[#ccff00]">
            <span>&larr; Back to History</span>
        </a>
         <button onclick="window.print()" class="border-2 border-white px-6 py-2 flex items-center gap-2 text-sm font-tech uppercase cursor-pointer transition hover:bg-white hover:text-black">
            <span>🖨️ Print Receipt</span>
        </button>
    </div>

    <!-- RECEIPT -->
    <div class="paper-block p-8 shadow-2xl relative bg-white text-black print:shadow-none print:border print:border-gray-300 print:p-0" id="receipt">
        
        <!-- Header -->
        <div class="text-center border-b border-black pb-6 mb-8">
             <h1 class="text-4xl font-display uppercase tracking-tighter leading-none mb-2">
                ARTEFACT.ROOM
            </h1>
            <div class="flex justify-center items-center gap-4 text-[10px] text-gray-500 uppercase tracking-widest">
                <span>Official Receipt</span>
                <span>&bull;</span>
                <span>{{ $order->created_at->format('d M Y / H:i') }}</span>
            </div>
        </div>

        <!-- ACCESS BLOCK -->
        <div class="border-2 border-dashed border-gray-400 p-5 mb-8 bg-gray-50 print:bg-transparent relative group">
            <div class="absolute top-0 left-0 bg-black text-white text-[9px] font-tech uppercase px-2 py-1 transform -translate-y-1/2 ml-4 border border-black">
                Customer Access Credentials
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                <!-- Login -->
                <div>
                    <span class="block text-[10px] text-gray-500 uppercase mb-1">Login ID</span>
                    <div class="font-mono font-bold text-sm flex items-center gap-2">
                        <span>{{ $order->customer_phone }}</span>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <span class="block text-[10px] text-gray-500 uppercase mb-1">Current Password</span>
                    <div class="flex items-center gap-2">
                        @if($order->user && $order->user->access_key)
                            <span id="pwd-text" class="font-mono font-black text-xl tracking-widest text-[#ccff00]">
                                {{ $order->user->access_key }}
                            </span>
                            
                            <!-- Copy Button -->
                            <button onclick="copyPassword('{{ $order->user->access_key }}')" class="no-print opacity-50 hover:opacity-100 transition" title="Copy Password">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                            <span id="copy-msg" class="text-[9px] text-green-600 font-bold hidden transition">Copied!</span>
                        @else
                            <span class="text-sm italic text-gray-500">Not set (Guest)</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-3 text-[10px] text-gray-500 border-t border-gray-300 pt-2 flex items-start gap-2 no-print">
                <span class="text-lg leading-none">&uarr;</span>
                <span>You can use these credentials to log in at any time.</span>
            </div>
        </div>

        <!-- Order Info -->
        <div class="grid grid-cols-2 gap-8 mb-8 text-sm">
            <div>
                <h4 class="font-bold uppercase text-[10px] text-gray-500 mb-2 border-b border-gray-300 inline-block pb-0.5">Bill To</h4>
                <p class="font-bold text-base">{{ $order->customer_name }}</p>
                <p class="text-xs font-mono mt-1">{{ $order->customer_phone }}</p>
                <p class="text-xs mt-1 opacity-80">{{ $order->shipping_address }}</p>
                <div class="mt-2">
                    <span class="text-[9px] font-bold uppercase bg-black text-white border border-black px-1 py-0.5">{{ $order->shipping_method }}</span>
                </div>
            </div>
            <div class="text-right">
                <h4 class="font-bold uppercase text-[10px] text-gray-500 mb-2 border-b border-gray-300 inline-block pb-0.5">Details</h4>
                <p class="font-bold text-xl font-mono">#{{ $order->order_number }}</p>
                <div class="mt-2">
                     <span class="inline-block px-3 py-1 text-[10px] font-bold uppercase border border-black bg-black text-white shadow-sm">
                        {{ $order->status }}
                    </span>
                </div>
                <p class="text-[10px] text-gray-500 mt-2 uppercase">{{ $order->payment_method }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-8">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b-2 border-black text-[10px] uppercase text-gray-500">
                        <th class="py-2 pl-2">Item Description</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-right pr-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr class="border-b border-gray-300 last:border-0 group hover:bg-gray-50 transition">
                            <td class="py-3 pl-2">
                                <span class="font-bold block">{{ $item->product_name }}</span>
                                <span class="text-[10px] text-gray-500 font-mono uppercase tracking-wide">{{ $item->sku }} / {{ $item->size }}</span>
                            </td>
                            <td class="py-3 text-center font-mono text-xs">{{ $item->quantity }}</td>
                            <td class="py-3 text-right font-mono font-bold pr-2">₴{{ number_format($item->total * 40, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total -->
        <div class="flex justify-end pt-4">
            <div class="w-full md:w-1/2 space-y-3">
                <div class="flex justify-between text-xs px-2">
                    <span class="text-gray-500 uppercase tracking-wider">Subtotal</span>
                    <span class="font-mono">₴{{ number_format($order->subtotal * 40, 0) }}</span>
                </div>
                 @if($order->discount_amount > 0)
                    <div class="flex justify-between text-xs px-2 text-red-500 font-bold">
                        <span class="uppercase tracking-wider">Discount</span>
                        <span class="font-mono">-₴{{ number_format($order->discount_amount * 40, 0) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-black text-2xl pt-4 border-t-2 border-black px-2 items-center">
                    <span class="uppercase text-sm tracking-widest">Total Paid</span>
                    <span class="text-[#ccff00]">₴{{ number_format($order->total_amount * 40, 0) }}</span>
                </div>
            </div>
        </div>

        <div class="mt-16 text-center text-[9px] text-gray-500 uppercase tracking-[0.2em] border-t border-gray-300 pt-4">
            Thank you for shopping at ARTEFACT.ROOM
        </div>
    </div>
    
    <div class="h-12 no-print"></div>
</div>

<script>
    function copyPassword(text) {
        navigator.clipboard.writeText(text).then(function() {
            const msg = document.getElementById('copy-msg');
            msg.classList.remove('hidden');
            setTimeout(() => msg.classList.add('hidden'), 2000);
        }, function(err) {
            console.error('Async: Could not copy text: ', err);
        });
    }
</script>

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; padding: 0 !important; margin: 0 !important; }
        body * { visibility: hidden; }
        #receipt, #receipt * { visibility: visible; }
        #receipt {
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
        #receipt * { color: black !important; border-color: #ddd !important; }
        #receipt h1 { font-size: 24pt !important; }
    }
</style>
@endsection