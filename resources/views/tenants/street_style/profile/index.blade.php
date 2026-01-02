<!-- FILE: resources/views/tenants/street_style/profile/index.blade.php -->
@extends('tenants.street_style.layouts.artefact')
@section('title', 'Profile - ARTEFACT.ROOM')

@section('content')
<div class="max-w-6xl mx-auto py-8">
    
    <!-- Welcome Banner -->
    @if(session('generated_password'))
    <div class="paper-block p-6 mb-8 border-2" style="border-color: #ccff00;">
        <h2 class="text-2xl font-display uppercase mb-2">⚠ New Account Details</h2>
        <div class="flex flex-col md:flex-row gap-8 items-start">
            <div class="flex-1">
                <p class="font-tech text-sm mb-4 text-gray-600">A secure account has been created for your order history.</p>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-black/10 border border-gray-700">
                        <span class="block text-[10px] text-gray-500 uppercase">Login</span>
                        <span class="font-mono font-bold text-white">{{ auth()->user()->phone }}</span>
                    </div>
                    <div class="p-3 bg-black/10 border border-gray-700">
                        <span class="block text-[10px] text-gray-500 uppercase">Password</span>
                        <span class="font-mono font-bold text-xl text-pink-500">{{ session('generated_password') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Sidebar -->
        <div class="col-span-1 space-y-6">
            
            <!-- Profile Data -->
            <div class="paper-block p-6">
                <h2 class="text-lg font-display uppercase mb-4 text-gray-500 border-b border-gray-700 pb-2">My Profile</h2>
                <div class="mb-6">
                    <p class="font-bold text-lg text-gray-600"">{{ auth()->user()->name }}</p>
                    <p class="text-sm text-gray-400 font-mono">{{ auth()->user()->phone }}</p>
                </div>
                
                <!-- Logout Form -->
                <form action="{{ route('client.logout') }}" method="POST" class="mb-6">
                    @csrf
                    <button class="w-full text-left text-red-500 font-bold hover:opacity-75 uppercase text-xs tracking-widest border border-red-500/30 p-2">
                        Disconnect
                    </button>
                </form>

                <!-- Password Update -->
                <h3 class="text-xs font-bold uppercase mb-3 text-gray-500">Update Credentials</h3>
                <form action="{{ route('client.password.update') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="password" name="current_password" placeholder="Current Password" class="w-full p-2 bg-black/50 border border-gray-700 text-white">
                    <input type="password" name="new_password" placeholder="New Password" class="w-full p-2 bg-black/50 border border-gray-700 text-white">
                    <input type="password" name="new_password_confirmation" placeholder="Confirm" class="w-full p-2 bg-black/50 border border-gray-700 text-white">
                    <button class="w-full border-2 border-white py-2 text-xs font-tech uppercase hover:bg-white hover:text-black transition">Save Changes</button>
                </form>
            </div>

            <!-- Support Form -->
            <div class="paper-block p-6 border-t-4" style="border-top-color: #ccff00;">
                <h2 class="text-lg font-display uppercase mb-4 text-gray-500">Support</h2>
                <p class="text-xs text-gray-400 mb-4">Have a question about an order?</p>
                
                <form action="{{ route('contact.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                    <input type="hidden" name="phone" value="{{ auth()->user()->phone }}">

                    <textarea name="message" rows="4" class="w-full p-2 bg-black/50 border border-gray-700 text-white text-xs" placeholder="Type your message here..." required></textarea>
                    
                    <button class="w-full border-2 border-white py-2 text-xs font-tech uppercase hover:bg-white hover:text-black transition flex items-center justify-center gap-2">
                        <span>✉ Send Message</span>
                    </button>
                </form>
            </div>

        </div>

        <!-- Main Content (Orders) -->
        <div class="col-span-3">
            <h1 class="text-3xl font-display uppercase mb-6 text-white">Order History</h1>
            
            @forelse($orders as $order)
                <div class="paper-block mb-4 p-6 hover:shadow-md transition group relative overflow-hidden">
                    <div class="absolute top-4 right-4">
                        <span class="px-3 py-1 text-[10px] uppercase font-bold bg-black border border-gray-700 text-white shadow-sm">
                            {{ $order->status }}
                        </span>
                    </div>

                    <div class="flex flex-col md:flex-row gap-6 items-center">
                        <div class="flex-1">
                            <div class="text-2xl font-bold font-mono text-gray-500">#{{ $order->order_number }}</div>
                            <div class="text-xs text-gray-400 uppercase mt-1">{{ $order->created_at->format('d F Y') }}</div>
                        </div>
                        
                        <div class="text-center px-8 border-l border-r border-gray-700 border-opacity-30">
                            <div class="text-xs text-gray-400 uppercase">Total</div>
                            <div class="text-xl font-bold font-mono text-pink-500">₴{{ number_format($order->total_amount * 40, 0) }}</div>
                        </div>

                        <div class="flex-1 text-right">
                             <a href="{{ route('client.orders.show', $order->id) }}" class="border-2 border-white px-6 py-2 text-xs font-tech uppercase inline-flex items-center gap-2 hover:bg-white hover:text-black transition">
                                <span>Receipt</span> &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="paper-block p-12 text-center border-dashed border-gray-700">
                    <p class="text-gray-400 italic mb-4">No records found.</p>
                    <a href="{{ route('home') }}" class="font-spray text-pink-500 text-sm uppercase">Go to Catalog</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection