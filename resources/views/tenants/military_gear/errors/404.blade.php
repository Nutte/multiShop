@extends('tenants.military_gear.layouts.military')

@section('title', 'Sector Not Found | KARAKURT')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center text-center px-4 relative overflow-hidden">
    <!-- Decorative Background Code -->
    <div class="absolute inset-0 z-0 opacity-10 pointer-events-none overflow-hidden font-mono text-[10px] text-military-accent break-all leading-none">
        01001000 01000101 01001100 01010000 00100000 01001101 01000101 00100001 00001010 01010011 01011001 01010011 01010100 01000101 01001101 00100000 01000110 01000001 01001001 01001100 01010101 01010010 01000101 00001010 01000011 01001111 01000100 01000101 00100000 01010010 01000101 01000100 00001010 01000100 01000001 01010100 01000001 00100000 01001100 01001111 01010011 01010100 00001010
    </div>

    <div class="relative z-10 max-w-xl">
        <div class="text-[10rem] font-bold text-military-gray leading-none relative inline-block">
            404
            <div class="absolute inset-0 text-military-accent animate-glitch opacity-50">404</div>
        </div>
        <h2 class="text-2xl md:text-3xl font-bold uppercase text-white mb-4 tracking-widest">
            Sector Not Found
        </h2>
        <p class="text-military-text font-mono text-sm mb-8">
            // WARNING: You have ventured into uncharted territory. <br>
            Coordinates corrupted or mission abandoned.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="px-8 py-4 bg-military-accent text-black font-bold uppercase tracking-widest hover:bg-white transition-colors">
                Return to Base
            </a>
            <a href="{{ route('shop.products') }}" class="px-8 py-4 border border-military-gray text-white font-mono uppercase tracking-widest hover:bg-military-gray/50 transition-colors">
                Access Armory
            </a>
        </div>
    </div>
</div>
@endsection