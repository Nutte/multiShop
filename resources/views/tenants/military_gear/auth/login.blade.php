@extends('tenants.military_gear.layouts.military')
@section('title', 'Identity Check')

@section('content')
<div class="h-[80vh] flex items-center justify-center">
    <div class="w-full max-w-md px-4">
        <div class="tech-border bg-military-dark p-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-military-accent"></div>
            <div class="absolute top-0 right-0 w-20 h-20 border-t border-r border-white/10"></div>
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-white uppercase tracking-wider">Identity Check</h2>
                <p class="text-xs font-mono text-military-text mt-2">ACCESS RESTRICTED AREA</p>
            </div>
            <div class="flex border-b border-military-gray mb-6">
                <button class="flex-1 py-2 text-sm font-bold uppercase text-military-accent border-b-2 border-military-accent">Login</button>
                <button class="flex-1 py-2 text-sm font-bold uppercase text-military-text hover:text-white">Register</button>
            </div>
            <form action="{{ route('client.login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <input type="text" name="phone" class="w-full bg-black border-b border-military-gray text-white px-0 py-3 focus:border-military-accent focus:outline-none font-mono text-sm placeholder-zinc-700 transition-colors" placeholder="PHONE // ID" required>
                </div>
                <div>
                    <input type="password" name="password" class="w-full bg-black border-b border-military-gray text-white px-0 py-3 focus:border-military-accent focus:outline-none font-mono text-sm placeholder-zinc-700 transition-colors" placeholder="PASSWORD" required>
                </div>
                <div class="flex justify-between items-center text-[10px] font-mono mt-2">
                    <label class="flex items-center gap-2 text-military-text cursor-pointer hover:text-white">
                        <input type="checkbox" name="remember" class="accent-military-accent"> REMEMBER ME
                    </label>
                    <a href="#" class="text-military-text hover:text-white underline">LOST KEY?</a>
                </div>
                <button class="w-full mt-8 border border-white text-white font-bold uppercase py-3 hover:bg-white hover:text-black transition-colors">
                    Authenticate
                </button>
            </form>
            <div class="mt-6 pt-6 border-t border-white/5 text-center">
                <p class="text-xs text-zinc-600 font-mono">SECURE SERVER: ONLINE</p>
            </div>
        </div>
    </div>
</div>
@endsection