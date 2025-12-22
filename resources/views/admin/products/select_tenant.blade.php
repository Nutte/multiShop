<!-- FILE: resources/views/admin/products/select_tenant.blade.php -->
@extends('layouts.admin')
@section('title', 'Select Store')

@section('content')
    <div class="flex flex-col items-center justify-center h-96">
        <div class="bg-white p-8 rounded shadow-md text-center max-w-md">
            <div class="text-6xl mb-4">🏪</div>
            <h1 class="text-2xl font-bold mb-2">Select a Store</h1>
            <p class="text-gray-500 mb-6">To manage products, please switch to a specific store context.</p>
            
            <div class="space-y-2">
                @foreach(config('tenants.tenants') as $id => $data)
                    <form action="{{ route('admin.switch_tenant') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tenant_id" value="{{ $id }}">
                        <button class="w-full bg-gray-100 hover:bg-blue-50 text-gray-800 font-semibold py-2 px-4 border border-gray-300 rounded shadow-sm">
                            Manage {{ $data['name'] }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
@endsection