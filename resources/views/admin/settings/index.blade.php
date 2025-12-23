<!-- FILE: resources/views/admin/settings/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Platform Settings')

@section('content')
    <div x-data="{ activeTab: 'general' }">
        
        <!-- Header & Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <h1 class="text-2xl font-bold mb-4">Platform Settings</h1>
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'general'" 
                        :class="activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    General Settings
                </button>
                <button @click="activeTab = 'telegram'" 
                        :class="activeTab === 'telegram' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition flex items-center gap-2">
                    <span class="text-xl">✈️</span> Telegram Bots
                </button>
            </nav>
        </div>

        <!-- TAB 1: GENERAL SETTINGS -->
        <div x-show="activeTab === 'general'" x-transition:enter.duration.300ms>
            <div class="bg-white p-6 rounded shadow max-w-2xl">
                <h2 class="text-lg font-bold mb-4 text-gray-700">Global Configuration</h2>
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2">Platform Name</label>
                        <input type="text" name="platform_name" value="TriShop Platform" class="w-full border p-2 rounded">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold mb-2">Admin Email</label>
                        <input type="email" name="admin_email" value="admin@trishop.com" class="w-full border p-2 rounded">
                    </div>
                    <div class="p-4 bg-yellow-50 text-yellow-800 text-sm rounded border border-yellow-200 mb-4">
                        This section is for global platform settings. Store-specific settings are managed within each store context.
                    </div>
                    <button class="bg-gray-800 text-white px-6 py-2 rounded font-bold hover:bg-gray-700">Save General Settings</button>
                </form>
            </div>
        </div>

        <!-- TAB 2: TELEGRAM SETTINGS -->
        <div x-show="activeTab === 'telegram'" x-transition:enter.duration.300ms style="display: none;">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- LEFT COL: CONFIG LIST -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- ADD NEW FORM -->
                    <div class="bg-white p-6 rounded shadow border-t-4 border-blue-600">
                        <h2 class="text-lg font-bold mb-4">Add New Bot</h2>
                        <form action="{{ route('admin.settings.telegram.store') }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-bold mb-1">Friendly Name</label>
                                    <input type="text" name="name" placeholder="e.g. StreetStyle Notifications" class="w-full border p-2 rounded" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold mb-1">Assign to Store</label>
                                    <select name="tenant_id" class="w-full border p-2 rounded bg-gray-50">
                                        <option value="">-- General / Unassigned --</option>
                                        @foreach($tenants as $id => $data)
                                            <option value="{{ $id }}">{{ $data['name'] }} ({{ $id }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-bold mb-1">Bot Token</label>
                                    <input type="text" name="bot_token" placeholder="123456:ABC..." class="w-full border p-2 rounded font-mono text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold mb-1">Chat ID</label>
                                    <input type="text" name="chat_id" placeholder="-100..." class="w-full border p-2 rounded font-mono text-sm" required>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="is_active" id="is_active_new" value="1" checked class="h-4 w-4">
                                    <label for="is_active_new" class="text-sm font-bold">Active</label>
                                </div>
                                <button class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-500">Save Configuration</button>
                            </div>
                        </form>
                    </div>

                    <!-- EXISTING CONFIGS -->
                    <h3 class="text-lg font-bold text-gray-700">Active Configurations</h3>
                    @forelse($telegramConfigs as $config)
                        <div class="bg-white rounded shadow overflow-hidden border {{ $config->is_active ? 'border-gray-200' : 'border-red-200 bg-red-50' }}">
                            <form action="{{ route('admin.settings.telegram.update', $config->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="p-4 flex flex-col md:flex-row gap-4 items-start md:items-center bg-gray-50 border-b">
                                    <div class="flex-1">
                                        <input type="text" name="name" value="{{ $config->name }}" class="font-bold text-lg bg-transparent border-b border-dashed border-gray-400 focus:border-blue-500 outline-none w-full">
                                    </div>
                                    <div class="flex gap-2">
                                        <button class="bg-green-600 text-white px-3 py-1 rounded text-sm font-bold hover:bg-green-500">Update</button>
                                    </div>
                                </div>

                                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Assigned Store</label>
                                        <select name="tenant_id" class="w-full border p-2 rounded text-sm">
                                            <option value="">-- General --</option>
                                            @foreach($tenants as $id => $data)
                                                <option value="{{ $id }}" {{ $config->tenant_id === $id ? 'selected' : '' }}>
                                                    {{ $data['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-2 pt-6">
                                        <input type="checkbox" name="is_active" id="active_{{ $config->id }}" value="1" {{ $config->is_active ? 'checked' : '' }}>
                                        <label for="active_{{ $config->id }}" class="text-sm">Is Active</label>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Bot Token</label>
                                        <input type="text" name="bot_token" value="{{ $config->bot_token }}" class="w-full border p-2 rounded font-mono text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Chat ID</label>
                                        <input type="text" name="chat_id" value="{{ $config->chat_id }}" class="w-full border p-2 rounded font-mono text-xs">
                                    </div>
                                </div>
                            </form>
                            <div class="p-2 bg-gray-100 border-t text-right">
                                <form action="{{ route('admin.settings.telegram.destroy', $config->id) }}" method="POST" onsubmit="return confirm('Delete this configuration?');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 text-xs font-bold hover:underline">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 italic py-4 bg-white rounded shadow">No Telegram bots configured yet.</p>
                    @endforelse
                </div>

                <!-- RIGHT COL: MANUAL SENDER -->
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded shadow border-t-4 border-indigo-500 sticky top-4">
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                            <span>📢</span> Manual Message
                        </h2>
                        <p class="text-xs text-gray-500 mb-4">Select a bot and send a custom message directly to the configured Chat ID.</p>
                        
                        <form action="{{ route('admin.settings.telegram.sendMessage') }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="block text-sm font-bold mb-1">Select Bot</label>
                                <select name="config_id" class="w-full border p-2 rounded bg-indigo-50" required>
                                    @if($telegramConfigs->isEmpty())
                                        <option value="" disabled selected>No active bots</option>
                                    @else
                                        @foreach($telegramConfigs as $config)
                                            <option value="{{ $config->id }}">{{ $config->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-bold mb-1">Message</label>
                                <textarea name="message" rows="4" class="w-full border p-2 rounded" placeholder="Type your message here... (Markdown supported)" required></textarea>
                            </div>

                            <button class="w-full bg-indigo-600 text-white px-4 py-3 rounded font-bold hover:bg-indigo-500 flex justify-center items-center gap-2 shadow-lg">
                                <span>🚀</span> Send Message
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection