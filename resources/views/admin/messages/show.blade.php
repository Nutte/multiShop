<!-- FILE: resources/views/admin/messages/show.blade.php -->
@extends('layouts.admin')
@section('title', 'Read Message')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('admin.messages.index', ['tenant_id' => $tenantId]) }}" class="text-blue-600 hover:underline font-bold">&larr; Back to Inbox</a>
            
            <form action="{{ route('admin.messages.destroy', [$message->id, 'tenant_id' => $tenantId]) }}" method="POST" onsubmit="return confirm('Delete this message?');">
                @csrf
                @method('DELETE')
                <button class="text-red-500 hover:text-red-700 font-bold text-sm bg-red-50 px-3 py-1 rounded border border-red-200">
                    Delete Message
                </button>
            </form>
        </div>

        <!-- USER PROFILE LINK (Если есть привязка) -->
        @if($message->user_id)
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 flex justify-between items-center shadow-sm">
                <div>
                    <p class="text-sm font-bold text-blue-900">Verified Customer</p>
                    <p class="text-xs text-blue-700">This message was sent from a logged-in user profile.</p>
                </div>
                <a href="{{ route('admin.users.show', ['user' => $message->user_id, 'tenant_id' => $tenantId]) }}" class="bg-blue-600 text-white px-4 py-2 rounded text-xs font-bold hover:bg-blue-500 shadow">
                    👤 View Customer Profile
                </a>
            </div>
        @endif

        <div class="bg-white rounded shadow-lg overflow-hidden border-t-4 border-blue-600">
            <div class="p-6 border-b bg-gray-50 flex justify-between items-start">
                <div>
                    <h1 class="text-xl font-bold mb-1">Message from {{ $message->email }}</h1>
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <span>{{ $message->created_at->format('d F Y, H:i') }}</span>
                        @if($message->phone)
                            <span class="bg-gray-200 px-2 py-0.5 rounded text-xs font-bold text-gray-700">Phone: {{ $message->phone }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <span class="block text-xs font-bold text-gray-400 uppercase">Received in Store</span>
                    <span class="font-bold text-gray-800">{{ config("tenants.tenants.{$tenantId}.name") }}</span>
                </div>
            </div>

            <div class="p-8 text-gray-800 leading-relaxed text-lg whitespace-pre-wrap">
{{ $message->message }}
            </div>

            <div class="p-6 bg-gray-50 border-t flex gap-4">
                <a href="mailto:{{ $message->email }}" class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-500">
                    Reply via Email
                </a>
                @if($message->phone)
                    <a href="tel:{{ $message->phone }}" class="bg-white border border-gray-300 text-gray-700 px-6 py-2 rounded font-bold hover:bg-gray-100">
                        Call {{ $message->phone }}
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection