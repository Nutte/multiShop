@extends('tenants.designer_hub.layouts.gadyuka')
@section('title', 'Receipt #' . $order->order_number)

@section('content')
<div class="min-h-screen bg-white text-black p-8 font-['JetBrains_Mono']">
    <div class="max-w-3xl mx-auto border-4 border-black p-8 relative print:border-none" id="receipt">
        
        <!-- ПАНЕЛЬ ДЕЙСТВИЙ -->
        <div class="flex justify-between items-center mb-12 border-b-4 border-black pb-6 no-print">
            <a href="{{ route('client.profile') }}" class="text-xs text-gray-600 hover:text-black uppercase flex items-center gap-2">
                <span>&larr; [BACK_TO_HISTORY]</span>
            </a>
            <button onclick="window.print()" class="bg-black text-white font-mono text-sm px-6 py-2 border border-black hover:bg-white hover:text-black transition-colors cursor-pointer flex items-center gap-2">
                <span>🖨️ [PRINT_RECORD]</span>
            </button>
        </div>

        <!-- Шапка чека -->
        <div class="flex justify-between items-start mb-12 border-b-4 border-black pb-6">
            <div>
                @php
                    $tenantId = app(\App\Services\TenantService::class)->getCurrentTenantId();
                    $tenantName = config("tenants.tenants.{$tenantId}.name", 'GADYUKA');
                @endphp
                <h1 class="font-['Space_Grotesk'] font-black text-5xl uppercase">{{ $tenantName }}</h1>
                <p class="text-xs tracking-[0.5em] mt-2 font-bold">OFFICIAL_RECORD</p>
            </div>
            <div class="text-right">
                <div class="border-2 border-black font-bold px-4 py-1 text-xl inline-block bg-black text-white transform -rotate-2">
                    {{ strtoupper($order->status) }}
                </div>
            </div>
        </div>

        <!-- БЛОК ДОСТУПА (КРЕДЕНШЕЛЫ) - ВСЕГДА АКТУАЛЕН -->
        @if($order->user && $order->user->access_key)
        <div class="border-2 border-dashed border-gray-400 p-5 mb-8 bg-gray-50 relative">
            <div class="absolute top-0 left-0 bg-black text-white font-['JetBrains_Mono'] text-[9px] font-bold uppercase px-2 py-1 transform -translate-y-1/2 ml-4 border border-black">
                [ACCESS_CREDENTIALS]
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                <!-- Логин -->
                <div>
                    <span class="block font-['JetBrains_Mono'] text-[10px] text-gray-600 uppercase mb-1">Login ID</span>
                    <div class="font-['JetBrains_Mono'] font-bold text-sm flex items-center gap-2">
                        <span>{{ $order->customer_phone }}</span>
                    </div>
                </div>

                <!-- Пароль (Актуальный Access Key) -->
                <div>
                    <span class="block font-['JetBrains_Mono'] text-[10px] text-gray-600 uppercase mb-1">Access Key</span>
                    <div class="flex items-center gap-2">
                        <span id="pwd-text" class="font-['JetBrains_Mono'] font-black text-xl tracking-widest text-[#ff003c]">
                            {{ $order->user->access_key }}
                        </span>
                        
                        <!-- Кнопка копирования -->
                        <button onclick="copyPassword('{{ $order->user->access_key }}')" class="no-print opacity-50 hover:opacity-100 transition" title="Copy Password">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </button>
                        <span id="copy-msg" class="font-['JetBrains_Mono'] text-[9px] text-green-600 font-bold hidden transition">[COPIED]</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 font-['JetBrains_Mono'] text-[10px] text-gray-600 border-t border-gray-300 pt-2 flex items-start gap-2 no-print">
                <span class="text-lg leading-none">&uarr;</span>
                <span>You can use these credentials to log in at any time. If you change your password in profile, this receipt will update automatically.</span>
            </div>
        </div>
        @endif

        <!-- Информация о заказе -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 font-['JetBrains_Mono'] text-sm">
            <div>
                <h4 class="font-bold uppercase text-[10px] text-gray-600 mb-2 border-b border-gray-400 inline-block pb-0.5">[BILL_TO]</h4>
                <p class="font-['Space_Grotesk'] font-bold text-base">{{ $order->customer_name }}</p>
                <p class="text-xs mt-1">{{ $order->customer_phone }}</p>
                <p class="text-xs mt-1 opacity-80">{{ $order->shipping_address }}</p>
                <div class="mt-2">
                    <span class="font-['JetBrains_Mono'] text-[9px] font-bold uppercase bg-black text-white px-2 py-1 border border-black">
                        {{ strtoupper($order->shipping_method) }}
                    </span>
                </div>
            </div>
            <div class="text-right">
                <h4 class="font-bold uppercase text-[10px] text-gray-600 mb-2 border-b border-gray-400 inline-block pb-0.5">[DETAILS]</h4>
                <p class="font-['Space_Grotesk'] font-bold text-2xl">#{{ $order->order_number }}</p>
                <div class="mt-2">
                    <span class="inline-block px-3 py-1 font-['JetBrains_Mono'] text-[10px] font-bold uppercase border-2 border-black bg-black text-white">
                        {{ strtoupper($order->status) }}
                    </span>
                </div>
                <p class="font-['JetBrains_Mono'] text-[10px] text-gray-600 mt-2 uppercase">{{ $order->payment_method }}</p>
            </div>
        </div>

        <!-- Таблица товаров -->
        <div class="mb-8">
            <table class="w-full text-left font-['JetBrains_Mono'] text-sm border-collapse">
                <thead>
                    <tr class="border-b-2 border-black text-[10px] uppercase text-gray-600">
                        <th class="py-2 pl-2">ITEM DESCRIPTION</th>
                        <th class="py-2 text-center">QTY</th>
                        <th class="py-2 text-right pr-2">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr class="border-b border-gray-300 last:border-0">
                            <td class="py-3 pl-2">
                                <span class="font-['Space_Grotesk'] font-bold block">{{ $item->product_name }}</span>
                                <span class="font-['JetBrains_Mono'] text-[10px] text-gray-600 uppercase tracking-wide">{{ $item->sku }} / {{ $item->size }}</span>
                            </td>
                            <td class="py-3 text-center font-bold">{{ $item->quantity }}</td>
                            <td class="py-3 text-right font-bold pr-2">${{ $item->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Итого -->
        <div class="flex justify-end pt-4">
            <div class="w-full md:w-1/2 space-y-3">
                <div class="flex justify-between font-['JetBrains_Mono'] text-xs px-2">
                    <span class="text-gray-600 uppercase tracking-wider">Subtotal</span>
                    <span class="font-bold">${{ $order->subtotal }}</span>
                </div>
                
                @if($order->discount_amount > 0)
                    <div class="flex justify-between font-['JetBrains_Mono'] text-xs px-2 text-red-600 font-bold">
                        <span class="uppercase tracking-wider">Discount</span>
                        <span>-${{ $order->discount_amount }}</span>
                    </div>
                @endif
                
                <div class="flex justify-between font-['Space_Grotesk'] font-black text-3xl pt-4 border-t-2 border-black px-2 items-center">
                    <span class="uppercase text-sm tracking-widest">TOTAL PAID</span>
                    <span class="text-[#ff003c]">${{ $order->total_amount }}</span>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center font-['JetBrains_Mono'] text-[9px] text-gray-600 uppercase tracking-[0.2em] border-t border-gray-300 pt-4">
            [TRANSMISSION_COMPLETE] // {{ $tenantName }}
        </div>

        <div class="mt-12 text-center print:hidden">
            <button onclick="window.print()" class="bg-black text-white font-bold py-3 px-8 hover:bg-gray-800 transition-colors uppercase">Print_Record</button>
            <a href="{{ route('client.profile') }}" class="ml-4 underline uppercase text-xs font-bold">Close_File</a>
        </div>
    </div>
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
            padding: 1cm !important;
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            background: white !important;
            color: black !important;
        }
        #receipt * { color: black !important; border-color: #ddd !important; }
        #receipt h1 { font-size: 24pt !important; }
        #receipt .text-\[\#ff003c\] { color: #000 !important; }
    }
</style>
@endsection