<!-- FILE: resources/views/client/profile/order.blade.php -->
@extends('layouts.app')
@section('title', 'Receipt #' . $order->order_number)

@section('content')
<div class="max-w-3xl mx-auto py-8">

    <!-- ПАНЕЛЬ ДЕЙСТВИЙ -->
    <div class="flex justify-between items-center mb-6 no-print">
        <a href="{{ route('client.profile') }}" class="theme-link text-xs font-bold uppercase flex items-center gap-2">
            <span>&larr; Back to History</span>
        </a>
         <button onclick="window.print()" class="theme-btn px-6 py-2 flex items-center gap-2 text-sm font-bold shadow-md cursor-pointer transition hover:opacity-90">
            <span>🖨️ Print Receipt</span>
        </button>
    </div>

    <!-- САМ ЧЕК -->
    <div class="theme-card p-8 shadow-2xl relative bg-white theme-text print:shadow-none print:border print:border-gray-300 print:p-0" id="receipt">
        
        <!-- Шапка -->
        <div class="text-center border-b theme-border pb-6 mb-8">
             <h1 class="text-4xl font-black uppercase tracking-tighter theme-skew leading-none mb-2">
                @php
                    $tenantId = app(\App\Services\TenantService::class)->getCurrentTenantId();
                    $tenantName = config("tenants.tenants.{$tenantId}.name", 'TriShop');
                @endphp
                {{ $tenantName }}
            </h1>
            <div class="flex justify-center items-center gap-4 text-[10px] theme-muted uppercase tracking-widest">
                <span>Official Receipt</span>
                <span>&bull;</span>
                <span>{{ $order->created_at->format('d M Y / H:i') }}</span>
            </div>
        </div>

        <!-- БЛОК ДОСТУПА (КРЕДЕНШЕЛЫ) - ВСЕГДА АКТУАЛЕН -->
        <div class="border-2 border-dashed theme-border p-5 mb-8 bg-gray-50 bg-opacity-5 print:bg-transparent relative group">
            <div class="absolute top-0 left-0 bg-gray-100 theme-text text-[9px] font-bold uppercase px-2 py-1 transform -translate-y-1/2 ml-4 theme-border border">
                Customer Access Credentials
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                <!-- Логин -->
                <div>
                    <span class="block text-[10px] theme-muted uppercase mb-1">Login ID</span>
                    <div class="font-mono font-bold text-sm flex items-center gap-2">
                        <span>{{ $order->customer_phone }}</span>
                    </div>
                </div>

                <!-- Пароль (Актуальный Access Key) -->
                <div>
                    <span class="block text-[10px] theme-muted uppercase mb-1">Current Password</span>
                    <div class="flex items-center gap-2">
                        @if($order->user && $order->user->access_key)
                            <!-- ВСЕГДА показываем актуальный ключ доступа (он же пароль) -->
                            <span id="pwd-text" class="font-mono font-black text-xl tracking-widest" style="color: var(--color-primary)">
                                {{ $order->user->access_key }}
                            </span>
                            
                            <!-- Кнопка копирования -->
                            <button onclick="copyPassword('{{ $order->user->access_key }}')" class="no-print opacity-50 hover:opacity-100 transition" title="Copy Password">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                            <span id="copy-msg" class="text-[9px] text-green-600 font-bold hidden transition">Copied!</span>
                        @else
                            <span class="text-sm italic theme-muted">Not set (Guest)</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-3 text-[10px] theme-muted border-t theme-border pt-2 flex items-start gap-2 no-print">
                <span class="text-lg leading-none">&uarr;</span>
                <span>You can use these credentials to log in at any time. If you change your password in profile, this receipt will update automatically.</span>
            </div>
        </div>

        <!-- Информация о заказе -->
        <div class="grid grid-cols-2 gap-8 mb-8 text-sm">
            <div>
                <h4 class="font-bold uppercase text-[10px] theme-muted mb-2 border-b theme-border inline-block pb-0.5">Bill To</h4>
                <p class="font-bold text-base">{{ $order->customer_name }}</p>
                <p class="text-xs font-mono mt-1">{{ $order->customer_phone }}</p>
                <p class="text-xs mt-1 opacity-80">{{ $order->shipping_address }}</p>
                <div class="mt-2">
                    <span class="text-[9px] font-bold uppercase theme-bg theme-border border px-1 py-0.5">{{ $order->shipping_method }}</span>
                </div>
            </div>
            <div class="text-right">
                <h4 class="font-bold uppercase text-[10px] theme-muted mb-2 border-b theme-border inline-block pb-0.5">Details</h4>
                <p class="font-bold text-xl font-mono">#{{ $order->order_number }}</p>
                <div class="mt-2">
                     <span class="inline-block px-3 py-1 text-[10px] font-bold uppercase border theme-border theme-bg shadow-sm">
                        {{ $order->status }}
                    </span>
                </div>
                <p class="text-[10px] theme-muted mt-2 uppercase">{{ $order->payment_method }}</p>
            </div>
        </div>

        <!-- Таблица товаров -->
        <div class="mb-8">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="border-b-2 theme-border text-[10px] uppercase theme-muted">
                        <th class="py-2 pl-2">Item Description</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-right pr-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr class="border-b theme-border last:border-0 group hover:bg-gray-50 transition">
                            <td class="py-3 pl-2">
                                <span class="font-bold block">{{ $item->product_name }}</span>
                                <span class="text-[10px] theme-muted font-mono uppercase tracking-wide">{{ $item->sku }} / {{ $item->size }}</span>
                            </td>
                            <td class="py-3 text-center font-mono text-xs">{{ $item->quantity }}</td>
                            <td class="py-3 text-right font-mono font-bold pr-2">${{ $item->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Итого -->
        <div class="flex justify-end pt-4">
            <div class="w-full md:w-1/2 space-y-3">
                <div class="flex justify-between text-xs px-2">
                    <span class="theme-muted uppercase tracking-wider">Subtotal</span>
                    <span class="font-mono">${{ $order->subtotal }}</span>
                </div>
                 @if($order->discount_amount > 0)
                    <div class="flex justify-between text-xs px-2 text-red-500 font-bold">
                        <span class="uppercase tracking-wider">Discount</span>
                        <span class="font-mono">-${{ $order->discount_amount }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-black text-2xl pt-4 border-t-2 theme-border px-2 items-center">
                    <span class="uppercase text-sm tracking-widest">Total Paid</span>
                    <span style="color: var(--color-primary)">${{ $order->total_amount }}</span>
                </div>
            </div>
        </div>

        <div class="mt-16 text-center text-[9px] theme-muted uppercase tracking-[0.2em] border-t theme-border pt-4">
            Thank you for shopping at {{ $tenantName }}
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