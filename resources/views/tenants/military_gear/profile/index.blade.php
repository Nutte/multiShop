<!-- FILE: resources/views/tenants/military_gear/profile/index.blade.php -->
@extends('layouts.app')
@section('title', 'Operator Profile')
@section('body_class', 'bg-stone-900 text-stone-100 font-mono')
@section('nav_class', 'bg-stone-800 border-b-4 border-orange-700')
@section('brand_name', 'MILITARY GEAR [TAC-OPS]')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4">

    @if(session('generated_password'))
        <div class="bg-orange-900/50 border-l-4 border-orange-600 p-6 mb-8">
            <h2 class="text-xl font-bold text-orange-500 mb-2">⚠ SECURITY ALERT: NEW CREDENTIALS ISSUED</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="bg-black/30 p-2">
                    <span class="text-stone-500 block">ACCESS ID</span>
                    <span class="text-white">{{ $user->phone }}</span>
                </div>
                <div class="bg-black/30 p-2">
                    <span class="text-stone-500 block">ACCESS KEY</span>
                    <span class="text-orange-400 font-bold tracking-widest text-lg">{{ session('generated_password') }}</span>
                </div>
            </div>
            <p class="text-xs text-stone-500 mt-2">PROTOCOL: Store these credentials securely. Update key immediately.</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar -->
        <div class="col-span-1 bg-stone-800 p-4 border border-stone-600 h-fit">
            <h2 class="text-orange-600 font-bold mb-4 border-b border-stone-600 pb-2 text-xs tracking-widest">PERSONNEL FILE</h2>
            <div class="mb-6 text-xs">
                <div class="mb-2 flex justify-between"><span class="text-stone-500">NAME:</span> <span>{{ strtoupper($user->name) }}</span></div>
                <div class="mb-2 flex justify-between"><span class="text-stone-500">ID:</span> <span>{{ $user->phone }}</span></div>
                <div class="flex justify-between"><span class="text-stone-500">STATUS:</span> <span class="text-green-500">ACTIVE</span></div>
            </div>

            <h2 class="text-orange-600 font-bold mb-4 border-b border-stone-600 pb-2 text-xs tracking-widest">SECURE ACCESS</h2>
            <form action="{{ route('client.password.update') }}" method="POST" class="space-y-2 mb-6">
                @csrf
                <input type="password" name="current_password" placeholder="CURRENT KEY" class="w-full bg-stone-900 border border-stone-700 p-2 text-xs text-white placeholder-stone-600 focus:border-orange-600 outline-none">
                <input type="password" name="new_password" placeholder="NEW KEY" class="w-full bg-stone-900 border border-stone-700 p-2 text-xs text-white placeholder-stone-600 focus:border-orange-600 outline-none">
                <input type="password" name="new_password_confirmation" placeholder="CONFIRM KEY" class="w-full bg-stone-900 border border-stone-700 p-2 text-xs text-white placeholder-stone-600 focus:border-orange-600 outline-none">
                <button class="bg-orange-800 text-white w-full py-2 text-xs font-bold hover:bg-orange-700 transition">UPDATE PROTOCOL</button>
            </form>

            <form action="{{ route('client.logout') }}" method="POST">
                @csrf
                <button class="w-full border border-red-900/50 text-red-500 py-2 text-xs font-bold hover:bg-red-900 hover:text-white transition">DISCONNECT</button>
            </form>
        </div>

        <!-- Main -->
        <div class="col-span-3">
            <h2 class="text-xl font-bold mb-6 text-white flex items-center gap-2">
                <span class="w-2 h-2 bg-orange-600 inline-block"></span>
                REQUISITION LOG
            </h2>
            
            <div class="overflow-hidden border border-stone-700">
                <table class="w-full text-left text-sm">
                    <thead class="bg-stone-800 text-stone-400 text-xs">
                        <tr>
                            <th class="p-3">ORDER ID</th>
                            <th class="p-3">TIMESTAMP</th>
                            <th class="p-3">STATUS</th>
                            <th class="p-3 text-right">VALUE</th>
                            <th class="p-3 text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-700 bg-stone-900/50">
                        @forelse($orders as $order)
                            <tr class="hover:bg-stone-800 transition">
                                <td class="p-3 font-bold text-white font-mono">{{ $order->order_number }}</td>
                                <td class="p-3 text-stone-400">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                <td class="p-3">
                                    <span class="bg-stone-700 border border-stone-600 text-orange-400 px-2 py-0.5 text-[10px] uppercase">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="p-3 text-right text-stone-300 font-mono">${{ $order->total_amount }}</td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('client.orders.show', $order->id) }}" class="text-[10px] border border-orange-600 text-orange-600 px-2 py-1 hover:bg-orange-600 hover:text-white transition">ACCESS DATA</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-12 text-center text-stone-500 border-t border-stone-700">NO RECORDS FOUND IN DATABASE</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection