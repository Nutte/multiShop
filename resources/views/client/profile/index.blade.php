<!-- FILE: resources/views/client/profile/index.blade.php -->
@extends('layouts.app')
@section('title', 'Profile')

@section('content')
<div class="max-w-6xl mx-auto py-8">
    
    <!-- Welcome Banner / New Account Alert -->
    @if(session('generated_password'))
        <div class="theme-card p-6 mb-8 border-2" style="border-color: var(--color-primary);">
            <h2 class="text-2xl font-bold uppercase mb-2 theme-text">⚠ New Account Details</h2>
            <div class="flex flex-col md:flex-row gap-8 items-start">
                <div class="flex-1">
                    <p class="theme-muted text-sm mb-4">A secure account has been created for your order history.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 bg-gray-900 bg-opacity-10 theme-border border">
                            <span class="block text-[10px] theme-muted uppercase">Login</span>
                            <span class="font-mono font-bold theme-text">{{ $user->phone }}</span>
                        </div>
                        <div class="p-3 bg-gray-900 bg-opacity-10 theme-border border">
                            <span class="block text-[10px] theme-muted uppercase">Password</span>
                            <span class="font-mono font-bold text-xl" style="color: var(--color-primary)">{{ session('generated_password') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Sidebar -->
        <div class="col-span-1 theme-card p-6 h-fit">
            <h2 class="text-lg font-bold uppercase mb-4 theme-muted border-b theme-border pb-2">My Profile</h2>
            <div class="mb-6">
                <p class="font-bold text-lg theme-text">{{ $user->name }}</p>
                <p class="text-sm theme-muted">{{ $user->phone }}</p>
            </div>
            
            <form action="{{ route('client.logout') }}" method="POST" class="mb-6">
                @csrf
                <button class="w-full text-left text-red-500 font-bold hover:opacity-75 uppercase text-xs tracking-widest border border-red-500/30 p-2 rounded">
                    Disconnect
                </button>
            </form>

            <h3 class="text-xs font-bold uppercase mb-3 theme-muted">Update Credentials</h3>
            <form action="{{ route('client.password.update') }}" method="POST" class="space-y-3">
                @csrf
                <input type="password" name="current_password" placeholder="Current Password" class="theme-input w-full p-2 text-xs">
                <input type="password" name="new_password" placeholder="New Password" class="theme-input w-full p-2 text-xs">
                <input type="password" name="new_password_confirmation" placeholder="Confirm" class="theme-input w-full p-2 text-xs">
                <button class="theme-btn w-full py-2 text-xs">Save Changes</button>
            </form>
        </div>

        <!-- Main Content -->
        <div class="col-span-3">
            <h1 class="text-3xl font-black uppercase mb-6 theme-skew theme-text">Order History</h1>
            
            @forelse($orders as $order)
                <div class="theme-card mb-4 p-6 hover:shadow-md transition group relative overflow-hidden">
                    <!-- Status Badge -->
                    <div class="absolute top-4 right-4">
                        <span class="px-3 py-1 text-[10px] uppercase font-bold theme-bg theme-border border shadow-sm theme-text">
                            {{ $order->status }}
                        </span>
                    </div>

                    <div class="flex flex-col md:flex-row gap-6 items-center">
                        <div class="flex-1">
                            <div class="text-2xl font-bold font-mono theme-text">#{{ $order->order_number }}</div>
                            <div class="text-xs theme-muted uppercase mt-1">{{ $order->created_at->format('d F Y') }}</div>
                        </div>
                        
                        <div class="text-center px-8 border-l border-r theme-border border-opacity-30">
                            <div class="text-xs theme-muted uppercase">Total</div>
                            <div class="text-xl font-bold font-mono" style="color: var(--color-primary)">${{ $order->total_amount }}</div>
                        </div>

                        <div class="flex-1 text-right">
                             <a href="{{ route('client.orders.show', $order->id) }}" class="theme-btn px-6 py-2 text-xs inline-flex items-center gap-2">
                                <span>Receipt</span> &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="theme-card p-12 text-center border-dashed">
                    <p class="theme-muted italic mb-4">No records found.</p>
                    <a href="{{ route('home') }}" class="theme-link font-bold text-sm uppercase">Go to Catalog</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection