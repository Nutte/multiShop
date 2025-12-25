<!-- FILE: resources/views/tenants/designer_hub/auth/login.blade.php -->
@extends('layouts.app')
@section('title', 'Sign In')
@section('body_class', 'bg-white text-gray-900 font-serif')
@section('nav_class', 'bg-white border-b border-gray-200 text-gray-900')
@section('brand_name', 'DESIGNER HUB')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center py-12">
    <div class="w-full max-w-sm">
        <div class="text-center mb-10">
            <h1 class="text-2xl font-light uppercase tracking-[0.2em] mb-2">Welcome Back</h1>
            <p class="text-xs text-gray-400 font-sans uppercase tracking-widest">Please identify yourself</p>
        </div>

        <form action="{{ url('/login') }}" method="POST" class="space-y-8">
            @csrf
            
            <div class="relative group">
                <input type="text" name="phone" placeholder=" " class="block w-full border-b border-gray-300 bg-transparent py-2 text-sm focus:border-black focus:outline-none transition peer">
                <label class="absolute left-0 -top-3.5 text-xs text-gray-400 transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:top-2 peer-placeholder-shown:text-gray-400 peer-focus:-top-3.5 peer-focus:text-xs peer-focus:text-black uppercase tracking-widest">
                    Phone Number
                </label>
            </div>

            <div class="relative group">
                <input type="password" name="password" placeholder=" " class="block w-full border-b border-gray-300 bg-transparent py-2 text-sm focus:border-black focus:outline-none transition peer">
                <label class="absolute left-0 -top-3.5 text-xs text-gray-400 transition-all peer-placeholder-shown:text-sm peer-placeholder-shown:top-2 peer-placeholder-shown:text-gray-400 peer-focus:-top-3.5 peer-focus:text-xs peer-focus:text-black uppercase tracking-widest">
                    Password
                </label>
            </div>

            <button class="w-full bg-black text-white py-4 text-xs font-bold uppercase tracking-[0.2em] hover:bg-gray-800 transition">
                Sign In
            </button>
        </form>

        <div class="mt-12 text-center">
            <a href="{{ route('home') }}" class="text-xs text-gray-400 hover:text-black border-b border-transparent hover:border-black transition pb-1">Return to Collection</a>
        </div>
    </div>
</div>
@endsection