<!-- FILE: resources/views/admin/inventory/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Inventory Report')

@section('content')
    <style>
        /* Стили ТОЛЬКО для печати */
        @media print {
            /* 1. Скрываем все лишнее */
            aside, nav, header, footer, .no-print, button { 
                display: none !important; 
            }

            /* 2. Сбрасываем ограничения ширины и отступы */
            body, main, .content, .container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                min-width: 100% !important;
            }

            /* 3. УБИРАЕМ СКРОЛЛЫ (Самое важное для разбиения на страницы) */
            .overflow-hidden, .overflow-x-auto, .overflow-y-auto {
                overflow: visible !important;
                height: auto !important;
            }

            /* 4. Настройки таблицы */
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 11px !important; /* Чуть меньше шрифт */
                table-layout: fixed !important; /* Фиксируем ширину колонок */
            }

            th, td {
                border: 1px solid #333 !important;
                padding: 4px !important;
                color: #000 !important;
                word-wrap: break-word; /* Перенос длинных слов */
            }

            /* 5. Повторять шапку на каждой странице */
            thead {
                display: table-header-group !important;
            }

            /* 6. Не разрывать строки таблицы посередине */
            tr {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            /* 7. Показывать заголовок печати */
            .print-only {
                display: block !important;
                margin-bottom: 20px;
            }

            /* Скрываем ссылки href при печати (чтобы не было URL после текста) */
            a { text-decoration: none !important; color: black !important; }
        }

        /* Скрываем блок для печати в обычном режиме */
        .print-only { display: none; }
    </style>

    <!-- Toolbar (Скрывается при печати) -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 no-print">
        <div>
            <h1 class="text-2xl font-bold">Inventory & Stock Report</h1>
            <p class="text-sm text-gray-500">Scope: 
                <span class="uppercase font-bold text-blue-600">
                    {{ $currentTenantId ? config("tenants.tenants.$currentTenantId.name") : 'ALL STORES (Global View)' }}
                </span>
            </p>
        </div>
        
        <div class="flex gap-2" x-data="{ showTelegramModal: false }">
            <!-- PRINT -->
            <button onclick="window.print()" class="bg-gray-700 text-white px-3 py-2 rounded font-bold hover:bg-gray-600 shadow flex items-center gap-2 text-sm">
                🖨️ Print PDF
            </button>
            
            <!-- EXPORT -->
            <button onclick="document.getElementById('exportFlag').value='export_csv'; document.getElementById('filterForm').submit(); document.getElementById('exportFlag').value='';" 
                    class="bg-green-600 text-white px-3 py-2 rounded font-bold hover:bg-green-500 shadow flex items-center gap-2 text-sm">
                📊 Export Excel
            </button>

            <!-- TELEGRAM BUTTON -->
            <button @click="showTelegramModal = true"
                    class="bg-blue-500 text-white px-3 py-2 rounded font-bold hover:bg-blue-400 shadow flex items-center gap-2 text-sm">
                ✈️ Send to Telegram
            </button>

            <!-- TELEGRAM MODAL -->
            <div x-show="showTelegramModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
                <div class="bg-white p-6 rounded shadow-lg w-96" @click.away="showTelegramModal = false">
                    <h3 class="text-lg font-bold mb-4">Send Report to Telegram</h3>
                    <form action="{{ route('admin.inventory.send_telegram') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tenant_id" value="{{ request('tenant_id') }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                        <input type="hidden" name="stock_status" value="{{ request('stock_status') }}">
                        <input type="hidden" name="clothing_line_id" value="{{ request('clothing_line_id') }}">

                        <div class="mb-4">
                            <label class="block text-sm font-bold mb-2">Select Bot / Chat</label>
                            <select name="telegram_config_id" class="w-full border p-2 rounded" required>
                                <option value="" disabled selected>Choose a bot...</option>
                                @foreach($telegramBots as $bot)
                                    <option value="{{ $bot->id }}">
                                        {{ $bot->name }} ({{ $bot->tenant_id ?? 'Global' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-end gap-2">
                            <button type="button" @click="showTelegramModal = false" class="bg-gray-200 px-4 py-2 rounded text-sm font-bold">Cancel</button>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded text-sm font-bold hover:bg-blue-500">Send Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- HEADER FOR PRINT ONLY -->
    <div class="print-only">
        <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">Inventory Report</h1>
        <p style="margin-bottom: 15px;">
            Scope: <strong>{{ $currentTenantId ?? 'ALL STORES' }}</strong> | 
            Date: <strong>{{ date('Y-m-d H:i') }}</strong>
        </p>
    </div>

    <!-- FILTERS (Hidden on print) -->
    <div class="bg-white p-4 rounded shadow mb-6 border border-gray-200 no-print">
        <form method="GET" action="{{ route('admin.inventory.index') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
            <input type="hidden" name="action" id="exportFlag" value="">

            @if(auth()->user()->role === 'super_admin')
                <div class="col-span-1">
                    <label class="block text-xs font-bold text-gray-500 mb-1">Store Scope</label>
                    <select name="tenant_id" class="w-full border p-2 rounded bg-yellow-50 border-yellow-200" onchange="this.form.submit()">
                        <option value="">ALL STORES (Global)</option>
                        @foreach(config('tenants.tenants') as $id => $data)
                            <option value="{{ $id }}" {{ $currentTenantId === $id ? 'selected' : '' }}>{{ $data['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">
            @endif

            <div class="col-span-1">
                <label class="block text-xs font-bold text-gray-500 mb-1">Stock Status</label>
                <select name="stock_status" class="w-full border p-2 rounded">
                    <option value="">All Statuses</option>
                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock (5+)</option>
                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock (< 5)</option>
                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                </select>
            </div>

            @if($currentTenantId)
                <div class="col-span-1">
                    <label class="block text-xs font-bold text-gray-500 mb-1">Collection</label>
                    <select name="clothing_line_id" class="w-full border p-2 rounded">
                        <option value="">All Collections</option>
                        @foreach($lines as $line)
                            <option value="{{ $line->id }}" {{ request('clothing_line_id') == $line->id ? 'selected' : '' }}>{{ $line->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="col-span-1 flex items-center text-xs text-gray-400 italic">
                    Select store to filter by Collection.
                </div>
            @endif

            <div class="col-span-2">
                <label class="block text-xs font-bold text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Product Name or SKU..." class="w-full border p-2 rounded">
            </div>

            <div class="col-span-1 flex gap-2">
                <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-500 font-bold">Filter</button>
                <a href="{{ route('admin.inventory.index', ['tenant_id' => $currentTenantId]) }}" class="bg-gray-200 text-gray-600 p-2 rounded hover:bg-gray-300">Reset</a>
            </div>
        </form>
    </div>

    <!-- TABLE CONTAINER -->
    <!-- Важно: убираем overflow-hidden при печати через CSS выше -->
    <div class="bg-white rounded shadow overflow-hidden print:shadow-none print:border-none">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b print:bg-gray-200">
                <tr>
                    <th class="p-3 text-xs font-bold text-gray-500 uppercase">Product</th>
                    @if(!$currentTenantId)<th class="p-3 text-xs font-bold text-gray-500 uppercase">Store</th>@endif
                    <th class="p-3 text-xs font-bold text-gray-500 uppercase">Price</th>
                    <th class="p-3 text-xs font-bold text-gray-500 uppercase">Stock Breakdown</th>
                    <th class="p-3 text-xs font-bold text-gray-500 uppercase text-center">Total</th>
                    <th class="p-3 text-xs font-bold text-gray-500 uppercase text-right">Value</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @php $grandTotalValue = 0; $grandTotalItems = 0; @endphp
                @forelse($products as $product)
                    @php 
                        $currentPrice = $product->sale_price ?? $product->price;
                        $totalValue = $product->stock_quantity * $currentPrice;
                        $grandTotalValue += $totalValue;
                        $grandTotalItems += $product->stock_quantity;
                    @endphp
                    <tr class="border-b hover:bg-gray-50 print:border-gray-400">
                        <td class="p-3 align-top">
                            <div class="font-bold text-gray-800">{{ $product->name }}</div>
                            <div class="text-xs text-gray-500 font-mono">{{ $product->sku }}</div>
                        </td>
                        @if(!$currentTenantId)
                            <td class="p-3 text-xs font-bold text-blue-600 align-top">{{ $product->tenant_name ?? '-' }}</td>
                        @endif
                        <td class="p-3 align-top">
                            @if($product->sale_price)
                                <span class="text-red-600 font-bold">${{ $product->sale_price }}</span>
                            @else
                                ${{ $product->price }}
                            @endif
                        </td>
                        <td class="p-3 align-top">
                            @if($product->variants->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($product->variants as $v)
                                        <span class="text-[10px] px-1 rounded border whitespace-nowrap {{ $v->stock <= 0 ? 'bg-red-50 border-red-200 text-red-600' : 'bg-gray-50 border-gray-200' }}">
                                            <b>{{ $v->size }}:</b> {{ $v->stock }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 italic text-xs">One Size</span>
                            @endif
                        </td>
                        <td class="p-3 text-center align-top">
                            @if($product->stock_quantity <= 0)
                                <span class="bg-red-100 text-red-800 px-2 py-0.5 rounded font-bold text-xs">0</span>
                            @elseif($product->stock_quantity < 5)
                                <span class="bg-orange-100 text-orange-800 px-2 py-0.5 rounded font-bold text-xs">{{ $product->stock_quantity }}</span>
                            @else
                                <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded font-bold text-xs">{{ $product->stock_quantity }}</span>
                            @endif
                        </td>
                        <td class="p-3 text-right font-mono align-top">
                            ${{ number_format($totalValue, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-400 italic">No inventory data found.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gray-100 border-t-2 border-gray-300 print:bg-white print:border-t print:border-black">
                <tr>
                    <td colspan="{{ !$currentTenantId ? 4 : 3 }}" class="p-3 text-right font-bold uppercase">Page Totals:</td>
                    <td class="p-3 text-center font-bold">{{ $grandTotalItems }}</td>
                    <td class="p-3 text-right font-bold">${{ number_format($grandTotalValue, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pagination (Hidden on print) -->
    <div class="mt-4 no-print">
        {{ $products->appends(request()->query())->links() }}
    </div>
@endsection