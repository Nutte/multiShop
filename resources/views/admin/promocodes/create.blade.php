<!-- FILE: resources/views/admin/promocodes/create.blade.php -->
@extends('layouts.admin')
@section('title', 'Create Promo Code')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.promocodes.index') }}" class="text-gray-500 hover:text-blue-600 font-bold">&larr; Back to List</a>
        <h1 class="text-2xl font-bold mt-2">Create New Promo Code</h1>
    </div>

    <form action="{{ route('admin.promocodes.store') }}" method="POST" class="max-w-5xl" 
          x-data="{ 
              scope: 'global',
              catalog: {{ json_encode($catalogData) }}
          }">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- LEFT COLUMN: Basic Info -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded shadow border-t-4 border-blue-600">
                    <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Basic Information</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1">Code</label>
                        <input type="text" name="code" placeholder="e.g. SUMMER2025" class="w-full border p-2 rounded uppercase font-mono text-lg" required>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-bold mb-1">Type</label>
                            <select name="type" class="w-full border p-2 rounded bg-gray-50">
                                <option value="percent">Percentage (%)</option>
                                <option value="fixed">Fixed Amount ($)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-1">Value</label>
                            <input type="number" name="value" placeholder="10" min="0" step="0.01" class="w-full border p-2 rounded" required>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mb-4">
                        <input type="checkbox" name="is_active" id="active" value="1" checked class="h-4 w-4">
                        <label for="active" class="text-sm font-bold">Is Active</label>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-1">Expires At (Optional)</label>
                        <input type="date" name="expires_at" class="w-full border p-2 rounded">
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Scope & Selection -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded shadow border-t-4 border-purple-600">
                    <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Applicability Scope</h3>

                    <!-- Scope Selector -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-6">
                        <label class="cursor-pointer">
                            <input type="radio" name="scope_type" value="global" x-model="scope" class="peer sr-only">
                            <div class="p-3 border rounded text-center peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 transition">
                                <div class="font-bold text-sm">Global</div>
                                <div class="text-[10px]">All Stores</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="scope_type" value="line" x-model="scope" class="peer sr-only">
                            <div class="p-3 border rounded text-center peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 transition">
                                <div class="font-bold text-sm">Collection</div>
                                <div class="text-[10px]">Specific Line</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="scope_type" value="category" x-model="scope" class="peer sr-only">
                            <div class="p-3 border rounded text-center peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 transition">
                                <div class="font-bold text-sm">Category</div>
                                <div class="text-[10px]">Specific Cat.</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="scope_type" value="specific" x-model="scope" class="peer sr-only">
                            <div class="p-3 border rounded text-center peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 hover:bg-gray-50 transition">
                                <div class="font-bold text-sm">Manual</div>
                                <div class="text-[10px]">Pick Products</div>
                            </div>
                        </label>
                    </div>

                    <!-- DYNAMIC CONTENT BASED ON SCOPE -->
                    <div class="bg-gray-50 p-4 rounded border border-gray-200">
                        
                        <!-- GLOBAL -->
                        <div x-show="scope === 'global'">
                            <p class="text-green-600 font-bold flex items-center gap-2">
                                ✅ This code will apply to ALL products in ALL stores.
                            </p>
                        </div>

                        <!-- SPECIFIC PRODUCTS -->
                        <div x-show="scope === 'specific'">
                            <p class="text-sm text-gray-500 mb-4">Select products from each store:</p>
                            <template x-for="(data, tenantId) in catalog" :key="tenantId">
                                <div class="mb-4 bg-white p-3 rounded border">
                                    <h4 class="font-bold text-gray-800 border-b pb-1 mb-2" x-text="data.name"></h4>
                                    <div class="h-40 overflow-y-auto space-y-1">
                                        <template x-for="prod in data.products" :key="prod.id">
                                            <label class="flex items-center gap-2 hover:bg-gray-50 p-1 rounded cursor-pointer">
                                                <input type="checkbox" :name="`scope_data[${tenantId}][]`" :value="prod.id" class="rounded text-blue-600">
                                                <span class="text-sm">
                                                    <span class="font-bold" x-text="prod.sku"></span> - <span x-text="prod.name"></span>
                                                </span>
                                            </label>
                                        </template>
                                        <div x-show="data.products.length === 0" class="text-xs text-gray-400 italic">No products found.</div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- CATEGORIES -->
                        <div x-show="scope === 'category'">
                            <p class="text-sm text-gray-500 mb-4">Select categories for each store:</p>
                            <template x-for="(data, tenantId) in catalog" :key="tenantId">
                                <div class="mb-4 bg-white p-3 rounded border">
                                    <h4 class="font-bold text-gray-800 border-b pb-1 mb-2" x-text="data.name"></h4>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="cat in data.categories" :key="cat.id">
                                            <label class="flex items-center gap-1 bg-gray-100 px-2 py-1 rounded border hover:bg-white cursor-pointer select-none">
                                                <input type="checkbox" :name="`scope_data[${tenantId}][]`" :value="cat.slug" class="rounded text-purple-600">
                                                <span class="text-xs font-bold" x-text="cat.name"></span>
                                            </label>
                                        </template>
                                        <div x-show="data.categories.length === 0" class="text-xs text-gray-400 italic">No categories.</div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- CLOTHING LINES -->
                        <div x-show="scope === 'line'">
                            <p class="text-sm text-gray-500 mb-4">Select collections for each store:</p>
                            <template x-for="(data, tenantId) in catalog" :key="tenantId">
                                <div class="mb-4 bg-white p-3 rounded border">
                                    <h4 class="font-bold text-gray-800 border-b pb-1 mb-2" x-text="data.name"></h4>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="line in data.lines" :key="line.id">
                                            <label class="flex items-center gap-1 bg-gray-100 px-2 py-1 rounded border hover:bg-white cursor-pointer select-none">
                                                <input type="checkbox" :name="`scope_data[${tenantId}][]`" :value="line.slug" class="rounded text-indigo-600">
                                                <span class="text-xs font-bold" x-text="line.name"></span>
                                            </label>
                                        </template>
                                        <div x-show="data.lines.length === 0" class="text-xs text-gray-400 italic">No collections.</div>
                                    </div>
                                </div>
                            </template>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 border-t pt-4 flex justify-end">
            <button class="bg-green-600 text-white font-bold py-3 px-8 rounded shadow hover:bg-green-500 text-lg">
                Create Promo Code
            </button>
        </div>
    </form>
@endsection