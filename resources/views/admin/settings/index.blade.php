<!-- FILE: resources/views/admin/settings/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Store Settings')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Store Integrations & Settings</h1>
    
    <div class="bg-white rounded shadow p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            
            <div class="mb-6 border-b pb-6">
                <h2 class="text-lg font-bold mb-4 text-blue-600">Telegram Notifications</h2>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Chat ID</label>
                    <input type="text" name="telegram_chat_id" 
                           value="{{ $settings['telegram_chat_id'] ?? '' }}"
                           class="w-full border p-2 rounded" 
                           placeholder="e.g. -100123456789">
                    <p class="text-xs text-gray-500 mt-1">
                        Use the Master Bot (@TriShopBot) and add it to your manager group.
                    </p>
                </div>
            </div>

            <div class="mb-6 border-b pb-6">
                <h2 class="text-lg font-bold mb-4 text-red-600">Delivery (Nova Poshta)</h2>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">API Key</label>
                    <input type="text" name="novaposhta_api_key" 
                           value="{{ $settings['novaposhta_api_key'] ?? '' }}"
                           class="w-full border p-2 rounded"
                           placeholder="Enter API Key">
                </div>
            </div>

            <button class="bg-blue-600 text-white font-bold py-2 px-6 rounded hover:bg-blue-500">
                Save Settings
            </button>
        </form>
    </div>
@endsection