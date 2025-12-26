<!-- FILE: resources/views/admin/messages/index.blade.php -->
@extends('layouts.admin')
@section('title', 'Inbox')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold flex items-center gap-2">
            <span>📬 Inbox</span>
            @if(auth()->user()->role === 'super_admin' && !$currentTenantId)
                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">All Stores</span>
            @endif
        </h1>
    </div>

    <!-- Toolbar -->
    <div class="bg-white p-4 rounded shadow mb-6 border border-gray-200">
        <form method="GET" action="{{ route('admin.messages.index') }}" class="flex items-end gap-4">
            @if(auth()->user()->role === 'super_admin')
                <div class="flex-1 max-w-xs">
                    <label class="block text-xs font-bold text-gray-500 mb-1">Store Context</label>
                    <select name="tenant_id" class="w-full border p-2 rounded bg-yellow-50 border-yellow-200" onchange="this.form.submit()">
                        <option value="">ALL STORES (Overview)</option>
                        @foreach(config('tenants.tenants') as $id => $data)
                            <option value="{{ $id }}" {{ $currentTenantId === $id ? 'selected' : '' }}>
                                {{ $data['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <div class="text-xs text-gray-500 pb-2">
                Showing latest messages sorted by date.
            </div>
        </form>
    </div>

    <!-- Messages List -->
    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-4 text-sm font-bold text-gray-600">Store</th>
                    <th class="p-4 text-sm font-bold text-gray-600">From</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Message Preview</th>
                    <th class="p-4 text-sm font-bold text-gray-600">Date</th>
                    <th class="p-4 text-sm font-bold text-gray-600 text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                    <tr class="border-b hover:bg-gray-50 {{ !$msg->is_read ? 'bg-blue-50' : '' }}">
                        <td class="p-4">
                            <span class="text-[10px] uppercase font-bold bg-gray-200 px-2 py-1 rounded text-gray-600">
                                {{ $msg->tenant_name ?? $msg->tenant_id }}
                            </span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <div class="font-bold {{ !$msg->is_read ? 'text-blue-700' : 'text-gray-800' }}">
                                    {{ $msg->email }}
                                </div>
                                @if($msg->user_id)
                                    <span class="text-xs" title="Registered User">👤</span>
                                @endif
                            </div>
                            @if($msg->phone)
                                <div class="text-xs text-gray-500">{{ $msg->phone }}</div>
                            @endif
                        </td>
                        <td class="p-4 max-w-md">
                            <div class="truncate text-gray-600 italic">"{{ Str::limit($msg->message, 50) }}"</div>
                        </td>
                        <td class="p-4 text-xs text-gray-500 whitespace-nowrap">
                            {{ $msg->created_at->format('d M Y, H:i') }}
                            @if(!$msg->is_read)
                                <span class="ml-2 w-2 h-2 bg-blue-500 rounded-full inline-block" title="Unread"></span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.messages.show', [$msg->id, 'tenant_id' => $msg->tenant_id]) }}" 
                               class="text-blue-600 hover:text-blue-800 font-bold text-sm">
                                Read
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-400 italic">No messages found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="p-4 border-t">
            {{ $messages->appends(request()->query())->links() }}
        </div>
    </div>
@endsection