<!-- FILE: resources/views/errors/404.blade.php -->
@extends('tenants.street_style.layouts.artefact')

@section('title', '404 // ARTEFACT.ROOM')

@section('content')
<div class="w-full pt-28 pb-20 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <div class="poster-card p-8 relative rotate-[-1deg] text-center">
            <!-- Tape Strip -->
            <div class="tape-strip -top-4 left-1/2 -translate-x-1/2 w-24 h-8 bg-white/40 rotate-2"></div>
            
            <!-- Error Code -->
            <div class="font-marker text-9xl text-gray-800 mb-2">404</div>
            
            <!-- Title -->
            <h1 class="font-syne font-black text-3xl uppercase mb-6">ARTIFACT MISSING</h1>
            
            <!-- Message -->
            <p class="font-tech text-gray-600 mb-8">
                The page you're looking for doesn't exist or has been moved.
            </p>
            
            <!-- Buttons -->
            <div class="space-y-4">
                <a href="{{ url('/') }}" 
                   class="block bg-black text-white font-syne font-bold py-3 px-6 hover:bg-neon hover:text-black transition uppercase">
                    ← Go to Homepage
                </a>
                <a href="javascript:history.back()" 
                   class="block border-2 border-black text-black font-tech py-3 px-6 hover:bg-black hover:text-white transition uppercase">
                    Go Back
                </a>
            </div>
            
            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-300">
                <p class="font-tech text-xs text-gray-500">
                    ERROR_CODE: 404-ARTFCT // SYSTEM_V3.0
                </p>
            </div>
        </div>
    </div>
</div>
@endsection