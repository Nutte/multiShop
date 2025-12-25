<!-- FILE: resources/views/tenants/street_style/profile/index.blade.php -->
@extends('layouts.app')
@section('title', 'My Base')
@section('body_class', 'bg-black text-white font-sans')
@section('nav_class', 'bg-black border-b border-yellow-400')
@section('brand_name', 'STREET STYLE')

@section('content')
<div class="max-w-6xl mx-auto">
    
    <!-- NEW ACCOUNT ALERT (Показывается только после первого заказа) -->
    @if(session('generated_password'))
        <div class="bg-yellow-400 text-black p-8 mb-8 border-4 border-white shadow-xl transform -skew-x-2">
            <h2 class="text-3xl font-black uppercase mb-2">🔥 Account Created!</h2>
            <p class="font-bold mb-4">We made a secure account for you. Save these credentials:</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-black text-white p-4">
                <div>
                    <span class="text-xs text-gray-500 uppercase block">Login (Phone)</span>
                    <span class="font-mono text-xl">{{ $user->phone }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase block">Password</span>
                    <span class="font-mono text-2xl text-yellow-400">{{ session('generated_password') }}</span>
                </div>
            </div>
            <p class="text-xs mt-2 font-bold uppercase">You can change this password below at any time.</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- SIDEBAR -->
        <div class="col-span-1 bg-gray-900 p-6 border-l-4 border-yellow-400 h-fit">
            <h2 class="text-xl font-black italic uppercase mb-4">Profile</h2>
            <div class="mb-6">
                <p class="font-bold text-yellow-400 text-lg">{{ $user->name }}</p>
                <p class="text-sm text-gray-400">{{ $user->phone }}</p>
            </div>
            
            <form action="{{ route('client.logout') }}" method="POST">
                @csrf
                <button class="w-full text-left text-red-500 font-bold hover:text-white uppercase text-sm transition">>> Log Out</button>
            </form>

            <hr class="border-gray-800 my-6">

            <h3 class="text-sm font-bold uppercase mb-4 text-gray-400">Security</h3>
            <form action="{{ route('client.password.update') }}" method="POST" class="space-y-3">
                @csrf
                <input type="password" name="current_password" placeholder="Current Pass" class="w-full bg-black border border-gray-700 p-2 text-sm text-white focus:border-yellow-400 outline-none">
                <input type="password" name="new_password" placeholder="New Pass" class="w-full bg-black border border-gray-700 p-2 text-sm text-white focus:border-yellow-400 outline-none">
                <input type="password" name="new_password_confirmation" placeholder="Confirm" class="w-full bg-black border border-gray-700 p-2 text-sm text-white focus:border-yellow-400 outline-none">
                <button class="bg-gray-800 text-white px-4 py-2 text-xs uppercase font-bold hover:bg-yellow-400 hover:text-black w-full transition">Update Password</button>
            </form>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col-span-3">
            <h1 class="text-5xl font-black italic mb-8 uppercase tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-500">
                Your Drops
            </h1>
            
            @forelse($orders as $order)
                <div class="bg-gray-900 mb-6 p-6 border border-gray-800 hover:border-yellow-400 transition group relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 pointer-events-none">
                        <span class="text-6xl font-black text-white">#{{ $loop->iteration }}</span>
                    </div>

                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="text-2xl font-black font-mono text-white">#{{ $order->order_number }}</span>
                                <span class="bg-yellow-400 text-black text-[10px] px-2 py-0.5 font-bold uppercase">{{ $order->status }}</span>
                            </div>
                            <div class="text-xs text-gray-500 uppercase mt-1">{{ $order->created_at->format('d F Y • H:i') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-yellow-400 font-mono">${{ $order->total_amount }}</div>
                            <div class="text-xs text-gray-500">{{ $order->items->count() }} items</div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-800 flex justify-between items-center">
                        <div class="flex -space-x-2">
                            @foreach($order->items->take(3) as $item)
                                <div class="w-8 h-8 rounded-full bg-gray-700 border-2 border-gray-900 flex items-center justify-center text-[8px] overflow-hidden" title="{{ $item->product_name }}">
                                    <!-- Заглушка, если нет картинки -->
                                    <div class="w-full h-full bg-gray-600"></div>
                                </div>
                            @endforeach
                            @if($order->items->count() > 3)
                                <div class="w-8 h-8 rounded-full bg-gray-800 border-2 border-gray-900 flex items-center justify-center text-[10px] text-gray-400">
                                    +{{ $order->items->count() - 3 }}
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ route('client.orders.show', $order->id) }}" class="inline-flex items-center gap-2 text-sm font-bold uppercase hover:text-yellow-400 transition">
                            <span>Receipt Details</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="border-2 border-dashed border-gray-800 p-12 text-center">
                    <p class="text-gray-500 text-xl italic mb-4">No history yet.</p>
                    <a href="{{ route('home') }}" class="bg-white text-black px-6 py-3 font-black uppercase text-sm hover:bg-yellow-400 transition">Start Shopping</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection