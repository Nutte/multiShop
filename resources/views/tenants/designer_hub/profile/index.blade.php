<!-- FILE: resources/views/tenants/designer_hub/profile/index.blade.php -->
@extends('layouts.app')
@section('title', 'Client Area')
@section('body_class', 'bg-white text-gray-900 font-serif')
@section('nav_class', 'bg-white border-b border-gray-200 text-gray-900')
@section('brand_name', 'DESIGNER HUB')

@section('content')
<div class="container mx-auto py-12 px-4">
    
    @if(session('generated_password'))
        <div class="max-w-4xl mx-auto bg-black text-white p-8 mb-12 text-center">
            <h2 class="text-2xl font-light uppercase tracking-widest mb-4">Welcome to Designer Hub</h2>
            <p class="mb-6 font-sans text-gray-400">Your personal account has been established.</p>
            <div class="inline-block border border-gray-700 p-6">
                <div class="mb-4">
                    <span class="block text-xs uppercase tracking-widest text-gray-500">Username</span>
                    <span class="font-mono text-lg">{{ $user->phone }}</span>
                </div>
                <div>
                    <span class="block text-xs uppercase tracking-widest text-gray-500">Temporary Password</span>
                    <span class="font-mono text-2xl border-b border-white pb-1">{{ session('generated_password') }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col md:flex-row gap-16 max-w-6xl mx-auto">
        <!-- Sidebar -->
        <aside class="w-full md:w-1/4 pt-2">
            <h3 class="font-bold uppercase text-xs tracking-widest mb-6 border-b border-black pb-2">My Profile</h3>
            <div class="mb-8">
                <p class="text-lg">{{ $user->name }}</p>
                <p class="text-sm text-gray-500 font-sans">{{ $user->phone }}</p>
            </div>

            <h3 class="font-bold uppercase text-xs tracking-widest mb-6 border-b border-black pb-2">Settings</h3>
            <form action="{{ route('client.password.update') }}" method="POST" class="space-y-4 mb-8">
                @csrf
                <input type="password" name="current_password" placeholder="Current Password" class="w-full border-b border-gray-300 py-2 text-sm focus:outline-none focus:border-black transition">
                <input type="password" name="new_password" placeholder="New Password" class="w-full border-b border-gray-300 py-2 text-sm focus:outline-none focus:border-black transition">
                <input type="password" name="new_password_confirmation" placeholder="Confirm New" class="w-full border-b border-gray-300 py-2 text-sm focus:outline-none focus:border-black transition">
                <button class="text-xs uppercase font-bold tracking-widest bg-black text-white px-6 py-3 w-full hover:bg-gray-800 transition">Save Changes</button>
            </form>

            <form action="{{ route('client.logout') }}" method="POST">
                @csrf
                <button class="text-xs uppercase tracking-widest text-gray-400 hover:text-black transition">Sign Out</button>
            </form>
        </aside>

        <!-- Orders -->
        <main class="w-full md:w-3/4">
            <h3 class="font-bold uppercase text-xs tracking-widest mb-8 border-b border-black pb-2">Purchase History</h3>
            
            <div class="space-y-6">
                @forelse($orders as $order)
                    <div class="flex flex-col sm:flex-row justify-between items-baseline border-b border-gray-100 pb-6 hover:bg-gray-50 transition p-4">
                        <div class="mb-2 sm:mb-0">
                            <span class="block font-mono text-lg">#{{ $order->order_number }}</span>
                            <span class="text-xs text-gray-400 uppercase tracking-widest">{{ $order->created_at->format('F d, Y') }}</span>
                        </div>
                        <div class="flex items-center gap-8">
                            <span class="text-xs uppercase tracking-widest px-2 py-1 bg-gray-100">{{ $order->status }}</span>
                            <span class="font-serif text-xl italic">${{ $order->total_amount }}</span>
                            <a href="{{ route('client.orders.show', $order->id) }}" class="w-8 h-8 flex items-center justify-center border border-black rounded-full hover:bg-black hover:text-white transition">&rarr;</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <p class="font-serif italic text-gray-400 text-xl mb-4">Your wardrobe is empty.</p>
                        <a href="{{ route('home') }}" class="text-xs uppercase font-bold border-b border-black pb-1 hover:text-gray-600">Discover Collection</a>
                    </div>
                @endforelse
            </div>
        </main>
    </div>
</div>
@endsection