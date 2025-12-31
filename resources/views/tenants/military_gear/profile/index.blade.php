@extends('tenants.military_gear.layouts.military')
@section('title', 'Mission Log')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid lg:grid-cols-4 gap-8">
        <!-- Sidebar (Dossier Menu) -->
        <div class="lg:col-span-1">
            <div class="tech-border bg-military-dark p-6 sticky top-24">
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-military-gray">
                    <div class="w-12 h-12 bg-military-gray flex items-center justify-center border border-military-text/30">
                        <svg class="w-6 h-6 text-military-text" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-white font-bold uppercase text-sm">{{ auth()->user()->name }}</h2>
                        <p class="text-xs font-mono text-military-accent">LEVEL: VETERAN</p>
                    </div>
                </div>
                
                <nav class="space-y-1">
                    <button class="w-full text-left px-4 py-3 bg-military-black border-l-2 border-military-accent text-white font-mono text-xs uppercase font-bold">
                        // Active Missions
                    </button>
                    <button class="w-full text-left px-4 py-3 hover:bg-military-black/50 border-l-2 border-transparent hover:border-military-text text-military-text hover:text-white transition-colors font-mono text-xs uppercase">
                        // Personal Data
                    </button>
                    <button class="w-full text-left px-4 py-3 hover:bg-military-black/50 border-l-2 border-transparent hover:border-military-text text-military-text hover:text-white transition-colors font-mono text-xs uppercase">
                        // Security Protocol
                    </button>
                    <form action="{{ route('client.logout') }}" method="POST" class="mt-6">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-3 text-red-500 hover:text-red-400 font-mono text-xs uppercase">
                            [ LOGOUT ]
                        </button>
                    </form>
                </nav>
            </div>
        </div>

        <!-- Content (Mission Log) -->
        <div class="lg:col-span-3">
            <h2 class="text-2xl font-bold text-white uppercase mb-6 flex items-center gap-3">
                <span class="w-3 h-3 bg-military-accent"></span> Mission Log (Orders)
            </h2>

            @forelse($orders as $order)
            <!-- Order Card -->
            <div class="tech-border bg-military-dark mb-4 group hover:border-military-text/50 transition-colors">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <span class="text-lg font-bold text-white">#{{ $order->order_number }}</span>
                                <span class="px-2 py-0.5 {{ $order->status === 'completed' ? 'bg-green-900/30 text-green-500 border border-green-900' : 'bg-yellow-900/30 text-yellow-500 border border-yellow-900' }} text-[10px] font-mono uppercase">{{ $order->status }}</span>
                            </div>
                            <p class="text-xs font-mono text-military-text">DATE: {{ $order->created_at->format('Y-m-d') }} // {{ $order->created_at->format('H:i') }} UTC</p>
                        </div>
                        <a href="{{ route('client.orders.show', $order->id) }}" class="mt-4 md:mt-0 px-4 py-2 border border-military-gray text-military-text hover:text-white hover:border-white font-mono text-xs uppercase flex items-center gap-2 transition-colors no-print">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Print Invoice
                        </a>
                    </div>
                    
                    <div class="bg-black/40 p-4 border border-military-gray/50 mb-4">
                        @foreach($order->items as $item)
                        <div class="flex items-center gap-4 mb-3 last:mb-0">
                            <div class="w-12 h-12 bg-zinc-800 overflow-hidden">
                                <img src="{{ $item->product->cover_url ?? '#' }}" class="w-full h-full object-cover opacity-70">
                            </div>
                            <div>
                                <p class="text-sm text-white font-bold uppercase">{{ $item->product_name }}</p>
                                <p class="text-xs font-mono text-military-text">QTY: {{ $item->quantity }} x {{ number_format($item->price) }} ₴</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="flex justify-between items-center text-sm font-mono border-t border-military-gray pt-4">
                        <span class="text-military-text">TOTAL DEPLOYED:</span>
                        <span class="text-white font-bold">{{ number_format($order->total_amount) }} ₴</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="tech-border bg-military-dark p-12 text-center">
                <p class="text-military-text italic mb-4">No mission records found.</p>
                <a href="{{ route('home') }}" class="text-military-accent font-bold text-sm uppercase hover:underline">Go to Supply Catalog</a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection