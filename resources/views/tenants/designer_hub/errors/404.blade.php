<!-- FILE: resources/views/errors/404.blade.php -->
@extends('tenants.designer_hub.layouts.gadyuka')

@section('title', '404 - Page Not Found')

@section('content')
<div class="flex items-center justify-center min-h-[80vh] px-4 relative overflow-hidden">
    <!-- Background Noise -->
    <div class="absolute inset-0 scanlines opacity-20"></div>
    <div class="absolute inset-0 speed-lines opacity-10"></div>
    
    <div class="text-center relative z-10">
        <div class="relative inline-block">
            <h1 class="text-9xl md:text-[12rem] font-display font-black text-white leading-none glitch-static">404</h1>
            <h1 class="text-9xl md:text-[12rem] font-display font-black text-brand-accent leading-none absolute top-1 left-1 -z-10 opacity-70">404</h1>
        </div>
        
        <div class="bg-black border-2 border-white p-6 max-w-md mx-auto mt-8 relative shadow-sharp-red">
            <div class="absolute -top-3 -left-3 bg-brand-accent text-white font-mono text-xs px-2 py-1 border border-black transform rotate-2">SYSTEM_ERROR</div>
            
            <h2 class="text-2xl font-bold uppercase mb-2">Page_Not_Found</h2>
            <p class="font-mono text-xs text-gray-400 mb-6 uppercase">
                The requested URL was not found on this server.<br>
                Link might be broken or page removed.
            </p>
            <p class="font-black text-brand-accent text-xl mb-6">ページが見つかりません</p>
            
            <a href="{{ route('home') }}" class="block w-full bg-white text-black font-display font-bold py-3 uppercase hover:bg-brand-accent hover:text-white transition-colors border-2 border-transparent text-center">
                Return to Base
            </a>
        </div>
    </div>
</div>
@endsection