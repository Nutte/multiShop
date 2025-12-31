@extends('tenants.designer_hub.layouts.gadyuka')
@section('title', 'Profile')

@section('content')
<div class="max-w-6xl mx-auto py-8">
    
    <!-- Welcome Banner -->
    @if(session('generated_password'))
        <div class="border-2 border-[#ff003c] bg-black p-6 mb-8 relative">
            <div class="absolute -top-3 left-4 bg-black px-2 font-['JetBrains_Mono'] text-xs text-[#ff003c] border border-[#ff003c]">NEW_ACCOUNT</div>
            
            <h2 class="text-2xl font-['Space_Grotesk'] font-bold uppercase mb-4">⚠ SYSTEM_ACCOUNT_CREATED</h2>
            <div class="flex flex-col md:flex-row gap-8 items-start">
                <div class="flex-1">
                    <p class="font-['JetBrains_Mono'] text-sm text-gray-400 mb-4">A secure account has been created for your order history.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-3 border border-gray-700 bg-gray-900/50">
                            <span class="block font-['JetBrains_Mono'] text-[10px] text-gray-500 uppercase">Login ID</span>
                            <span class="font-['JetBrains_Mono'] font-bold text-lg mt-1">{{ $user->phone }}</span>
                        </div>
                        <div class="p-3 border border-gray-700 bg-gray-900/50">
                            <span class="block font-['JetBrains_Mono'] text-[10px] text-gray-500 uppercase">Access Key</span>
                            <span class="font-['JetBrains_Mono'] font-bold text-xl mt-1 text-[#ff003c] tracking-widest">{{ session('generated_password') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col md:flex-row gap-8 border-2 border-white p-1 min-h-[60vh]">

        <!-- Sidebar -->
        <aside class="w-full md:w-64 bg-gray-900 p-6 flex flex-col border-r border-white">
            <div class="w-20 h-20 bg-white mb-4 overflow-hidden border border-gray-600">
                 <div class="w-full h-full bg-gray-800 flex items-center justify-center font-['JetBrains_Mono'] text-xs text-gray-500">
                    [AVATAR]
                 </div>
            </div>
            <h2 class="font-['Space_Grotesk'] font-bold text-xl">{{ strtoupper(substr($user->name, 0, 5)) }}_01</h2>
            <p class="font-['JetBrains_Mono'] text-xs text-[#ff003c] mb-8">STATUS: ACTIVE</p>

            <nav class="space-y-1">
                <a href="#" class="block px-3 py-2 bg-white text-black font-bold font-['JetBrains_Mono'] text-xs uppercase">> Orders</a>
                <a href="#" class="block px-3 py-2 text-gray-400 hover:text-white font-['JetBrains_Mono'] text-xs uppercase">> Address_Data</a>
                <form action="{{ route('client.logout') }}" method="POST" class="mt-8">
                    @csrf
                    <button type="submit" class="w-full text-center block px-3 py-2 text-[#ff003c] hover:text-white font-['JetBrains_Mono'] text-xs uppercase border border-[#ff003c]">
                        [LOGOUT]
                    </button>
                </form>
            </nav>
            
            <!-- Password Update -->
            <div class="mt-8 pt-6 border-t border-gray-700">
                <h3 class="font-['JetBrains_Mono'] text-xs text-gray-500 uppercase mb-3">UPDATE_CREDENTIALS</h3>
                <form action="{{ route('client.password.update') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="password" name="current_password" placeholder="Current Password" class="w-full border-b border-gray-600 bg-transparent py-2 font-['JetBrains_Mono'] text-white focus:outline-none focus:border-[#ff003c] placeholder-gray-700 text-xs">
                    <input type="password" name="new_password" placeholder="New Password" class="w-full border-b border-gray-600 bg-transparent py-2 font-['JetBrains_Mono'] text-white focus:outline-none focus:border-[#ff003c] placeholder-gray-700 text-xs">
                    <input type="password" name="new_password_confirmation" placeholder="Confirm" class="w-full border-b border-gray-600 bg-transparent py-2 font-['JetBrains_Mono'] text-white focus:outline-none focus:border-[#ff003c] placeholder-gray-700 text-xs">
                    <button class="w-full bg-white text-black font-['JetBrains_Mono'] text-xs py-2 border border-white hover:bg-black hover:text-white transition-colors uppercase">
                        [SAVE_CHANGES]
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content (Orders) -->
        <div class="flex-1 p-6">
            <h2 class="text-3xl font-['Space_Grotesk'] font-bold uppercase mb-8 border-b border-gray-800 pb-2">[MISSION_HISTORY]</h2>
            
            @forelse($orders as $order)
                <div class="border border-white p-4 flex justify-between items-center bg-black hover:bg-gray-900 transition-colors cursor-pointer group mb-4" onclick="window.location='{{ route('client.orders.show', $order->id) }}'">
                    <div class="flex items-center gap-4">
                        <div class="w-2 h-12 bg-[#ff003c] group-hover:bg-white transition-colors"></div>
                        <div>
                            <p class="font-['JetBrains_Mono'] text-[10px] text-gray-500">{{ $order->created_at->format('Y.m.d') }}</p>
                            <p class="font-['Space_Grotesk'] font-bold text-lg text-white">#{{ $order->order_number }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-['JetBrains_Mono'] font-bold text-lg">${{ $order->total_amount }}</p>
                        <div class="mt-1">
                            <span class="px-2 py-1 font-['JetBrains_Mono'] text-[9px] uppercase border border-white">
                                {{ strtoupper($order->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="border-2 border-white border-dashed bg-black p-12 text-center">
                    <p class="font-['JetBrains_Mono'] text-gray-400 italic mb-4">[NO_RECORDS_FOUND]</p>
                    <a href="{{ route('home') }}" class="font-['JetBrains_Mono'] text-sm text-[#ff003c] hover:text-white uppercase border-b border-[#ff003c]">[GO_TO_CATALOG]</a>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection