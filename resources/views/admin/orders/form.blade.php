<!-- FILE: resources/views/admin/orders/form.blade.php -->
@extends('layouts.admin')

@php
    $isEdit = isset($order);
    $title = $isEdit ? 'Edit Order #' . $order->order_number : 'New Order';
    $action = $isEdit ? route('admin.orders.update', $order->id) : route('admin.orders.store');
    
    $storeName = $currentTenantId ? config("tenants.tenants.{$currentTenantId}.name") : 'Select Store';
@endphp

@section('title', $title)

@section('content')

@if(!$isEdit && $isSuperAdmin && !$currentTenantId)
    <!-- ВЫБОР МАГАЗИНА -->
    <div class="max-w-2xl mx-auto mt-10">
        <div class="bg-white p-8 rounded shadow-lg border-t-4 border-blue-600 text-center">
            <h1 class="text-2xl font-bold mb-4">Start New Order</h1>
            <p class="text-gray-500 mb-6">Select a store to load products and customers.</p>
            <div class="inline-block w-full max-w-md text-left">
                <select onchange="if(this.value) window.location.href = '{{ route('admin.orders.create') }}?tenant_id=' + this.value"
                        class="w-full border p-3 rounded bg-yellow-50 border-yellow-300 font-bold text-gray-800">
                    <option value="">-- Choose Store --</option>
                    @foreach(config('tenants.tenants') as $id => $data)
                        <option value="{{ $id }}">🏪 {{ $data['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
@else
    <div class="max-w-6xl mx-auto" x-data="orderManager()">
        <form action="{{ $action }}" method="POST">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif
            
            <input type="hidden" name="tenant_id" value="{{ $currentTenantId }}">

            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold">{{ $title }}</h1>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm font-bold border border-blue-200">
                        {{ $storeName }}
                    </span>
                </div>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded font-bold shadow hover:bg-green-500 transition flex items-center gap-2">
                    <span>💾</span> Save Order
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- LEFT COLUMN -->
                <div class="space-y-6">
                    <div class="bg-white p-4 rounded shadow">
                        <h3 class="font-bold text-gray-700 mb-3 border-b pb-2 uppercase text-xs">Customer</h3>
                        <div class="space-y-3">
                            <!-- ВЫБОР КЛИЕНТА С ФИКСОМ ОТОБРАЖЕНИЯ -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Link to Profile (Optional)</label>
                                <select name="user_id" 
                                        x-model="selectedUserId" 
                                        @change="updateCustomerFields()" 
                                        x-init="$nextTick(() => { if(selectedUserId) $el.value = selectedUserId })"
                                        class="w-full border p-2 rounded text-sm bg-blue-50 border-blue-200 font-bold text-blue-900 focus:outline-none focus:border-blue-400">
                                    <option value="">-- Guest / New Customer --</option>
                                    <template x-for="c in customers" :key="c.id">
                                        <option :value="String(c.id)" x-text="c.name + ' (' + c.phone + ')'"></option>
                                    </template>
                                </select>
                                <p class="text-[10px] text-gray-400 mt-1">Selecting a user will lock Name, Phone and Email to their profile data.</p>
                            </div>

                            <hr class="border-gray-100 my-2">

                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Name</label>
                                <input type="text" name="customer_name" x-model="customer_name" :readonly="!!selectedUserId" 
                                       class="w-full border p-2 rounded text-sm transition"
                                       :class="{'bg-gray-100 text-gray-500 cursor-not-allowed': !!selectedUserId}" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Phone</label>
                                <input type="text" name="customer_phone" x-model="customer_phone" :readonly="!!selectedUserId" 
                                       class="w-full border p-2 rounded text-sm transition"
                                       :class="{'bg-gray-100 text-gray-500 cursor-not-allowed': !!selectedUserId}" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Email</label>
                                <input type="email" name="customer_email" x-model="customer_email" :readonly="!!selectedUserId" 
                                       class="w-full border p-2 rounded text-sm transition"
                                       :class="{'bg-gray-100 text-gray-500 cursor-not-allowed': !!selectedUserId}">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded shadow">
                        <h3 class="font-bold text-gray-700 mb-3 border-b pb-2 uppercase text-xs">Delivery & Details</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Shipping Method</label>
                                <select name="shipping_method" class="w-full border p-2 rounded text-sm bg-white">
                                    <option value="nova_poshta" {{ ($order->shipping_method ?? '') == 'nova_poshta' ? 'selected' : '' }}>Nova Poshta</option>
                                    <option value="courier" {{ ($order->shipping_method ?? '') == 'courier' ? 'selected' : '' }}>Courier</option>
                                    <option value="pickup" {{ ($order->shipping_method ?? '') == 'pickup' ? 'selected' : '' }}>Pickup</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Address / Branch</label>
                                <textarea name="shipping_address" rows="3" class="w-full border p-2 rounded text-sm" required>{{ old('shipping_address', $order->shipping_address ?? '') }}</textarea>
                            </div>
                            
                            <div class="pt-2 border-t mt-2">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Order Status</label>
                                @if($isEdit)
                                    <select name="status" class="w-full border p-2 rounded text-sm font-bold">
                                        @foreach(['new', 'processing', 'shipped', 'completed', 'cancelled'] as $st)
                                            <option value="{{ $st }}" {{ $order->status == $st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="status" value="new">
                                    <div class="text-sm font-bold text-blue-600 bg-blue-50 p-2 rounded border border-blue-100">NEW ORDER</div>
                                @endif
                            </div>

                            <label class="flex items-center gap-2 mt-4 cursor-pointer bg-pink-50 p-2 rounded border border-pink-100">
                                <input type="checkbox" name="is_instagram" value="1" {{ ($order->is_instagram ?? false) ? 'checked' : '' }} class="w-4 h-4 text-pink-600">
                                <span class="text-sm font-bold text-pink-600">Mark as Instagram Order</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: PRODUCTS -->
                <div class="lg:col-span-2">
                    <div class="bg-white p-4 rounded shadow border-t-4 border-yellow-500 h-full">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-gray-700 uppercase text-xs">Order Items</h3>
                            <div class="text-xs text-gray-400">Context: {{ $storeName }}</div>
                        </div>

                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="p-2 w-1/2">Product</th>
                                    <th class="p-2 w-24">Size</th>
                                    <th class="p-2 w-20 text-center">Qty</th>
                                    <th class="p-2 w-24 text-right">Price</th>
                                    <th class="p-2 w-8"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in rows" :key="index">
                                    <tr class="border-b hover:bg-gray-50 transition">
                                        <td class="p-2 align-top">
                                            <select :name="'items['+index+'][product_id]'" 
                                                    x-model="row.product_id" 
                                                    @change="updateRow(index)"
                                                    x-init="$nextTick(() => { if(row.product_id) $el.value = row.product_id })"
                                                    class="w-full border p-2 rounded text-xs bg-white focus:border-blue-500 outline-none">
                                                <option value="">-- Select Product --</option>
                                                <template x-for="p in catalog" :key="p.id">
                                                    <option :value="p.id" x-text="p.name + ' ($' + p.price + ')'"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="p-2 align-top">
                                            <template x-if="row.variants && row.variants.length > 0">
                                                <select :name="'items['+index+'][size]'" x-model="row.size" 
                                                        x-init="$nextTick(() => { if(row.size) $el.value = row.size })"
                                                        class="w-full border p-2 rounded text-xs bg-white">
                                                    <template x-for="v in row.variants" :key="v.id">
                                                        <option :value="v.size" x-text="v.size + ' (' + v.stock + ')'" :disabled="v.stock <= 0"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="!row.variants || row.variants.length === 0">
                                                <div class="pt-2">
                                                    <span class="text-xs text-gray-400 pl-1">One Size</span>
                                                    <input type="hidden" :name="'items['+index+'][size]'" value="One Size">
                                                </div>
                                            </template>
                                        </td>
                                        <td class="p-2 align-top text-center">
                                            <input type="number" :name="'items['+index+'][quantity]'" x-model="row.quantity" min="1" class="w-16 border p-2 rounded text-xs text-center mx-auto">
                                        </td>
                                        <td class="p-2 align-top text-right pt-3">
                                            <input type="hidden" :name="'items['+index+'][price]'" :value="row.price">
                                            <span class="font-bold text-gray-700" x-text="'$' + (parseFloat(row.price) * parseInt(row.quantity)).toFixed(2)"></span>
                                        </td>
                                        <td class="p-2 align-top text-center pt-2">
                                            <button type="button" @click="removeRow(index)" class="text-red-400 hover:text-red-600 font-bold text-lg">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="p-4 text-center border-b border-dashed">
                                        <button type="button" @click="addRow()" class="text-blue-600 text-xs font-bold uppercase hover:underline flex items-center justify-center gap-1 mx-auto">
                                            <span>+</span> Add Another Item
                                        </button>
                                    </td>
                                </tr>
                                <tr class="bg-gray-100 font-bold text-lg">
                                    <td colspan="3" class="p-4 text-right text-gray-600 text-xs uppercase tracking-wide">Estimated Total:</td>
                                    <td class="p-4 text-right text-blue-700" x-text="'$' + total"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function orderManager() {
            return {
                // Преобразуем ID в строки для стабильного сравнения в x-model
                catalog: (@json($productsJson ?? [])).map(p => ({...p, id: String(p.id)})),
                customers: (@json($customersJson ?? [])).map(c => ({...c, id: String(c.id)})),
                
                rows: [],
                
                // Данные клиента
                selectedUserId: '{{ $order->user_id ?? "" }}',
                customer_name: '{{ old("customer_name", $order->customer_name ?? "") }}',
                customer_phone: '{{ old("customer_phone", $order->customer_phone ?? "") }}',
                customer_email: '{{ old("customer_email", $order->customer_email ?? "") }}',

                init() {
                    // Инициализация строк товаров
                    @if($isEdit)
                        const itemsFromDb = @json($order->items);
                        // Сначала создаем пустые структуры
                        this.rows = itemsFromDb.map(item => ({
                            product_id: '', 
                            size: '', 
                            quantity: item.quantity, 
                            price: item.price, 
                            variants: [] 
                        }));

                        // Затем заполняем данные через тик
                        this.$nextTick(() => {
                            this.rows.forEach((row, index) => {
                                const dbItem = itemsFromDb[index];
                                const pId = String(dbItem.product_id);
                                const product = this.catalog.find(p => p.id === pId);
                                
                                row.product_id = pId;
                                row.variants = product ? (product.variants || []) : [];
                                row.size = dbItem.size;
                                row.price = dbItem.price;
                            });
                        });
                        
                        // Если есть привязанный юзер, форсируем его выбор тоже через тик
                        if (this.selectedUserId) {
                            this.selectedUserId = String(this.selectedUserId);
                        }
                    @else
                        this.addRow();
                    @endif
                },
                
                // Метод автозаполнения клиента
                updateCustomerFields() {
                    const user = this.customers.find(c => c.id === String(this.selectedUserId));
                    if (user) {
                        this.customer_name = user.name;
                        this.customer_phone = user.phone;
                        this.customer_email = user.email || '';
                    } else {
                        // Если Guest выбран вручную - поля остаются доступными для ввода
                        this.selectedUserId = '';
                    }
                },

                get total() {
                    return this.rows.reduce((sum, r) => {
                        const subtotal = parseFloat(r.price) * parseInt(r.quantity);
                        return sum + (isNaN(subtotal) ? 0 : subtotal);
                    }, 0).toFixed(2);
                },

                addRow() {
                    this.rows.push({ 
                        product_id: '', 
                        size: '', 
                        quantity: 1, 
                        price: 0, 
                        variants: [] 
                    });
                },

                removeRow(index) {
                    this.rows.splice(index, 1);
                    if (this.rows.length === 0) this.addRow(); 
                },

                updateRow(index) {
                    const row = this.rows[index];
                    const product = this.catalog.find(p => p.id === String(row.product_id));
                    
                    if (product) {
                        row.price = product.price;
                        row.variants = product.variants || [];
                        
                        const hasCurrentSize = row.variants.some(v => v.size === row.size);
                        if (!hasCurrentSize && row.variants.length > 0) {
                            row.size = row.variants[0].size;
                        } else if (row.variants.length === 0) {
                            row.size = 'One Size';
                        }
                    } else {
                        row.price = 0;
                        row.variants = [];
                        row.size = '';
                    }
                }
            }
        }
    </script>
@endif
@endsection