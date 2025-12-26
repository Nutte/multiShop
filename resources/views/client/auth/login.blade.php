<!-- FILE: resources/views/client/auth/login.blade.php -->
@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center p-4">
    <div class="w-full max-w-md theme-card p-8 shadow-2xl relative">
        
        <h1 class="text-3xl font-black uppercase mb-2 text-center theme-skew theme-text">
            Member Login
        </h1>
        <p class="text-center theme-muted mb-8 text-xs uppercase tracking-widest">Secure Access</p>

        <form action="{{ url('/login') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-xs font-bold uppercase theme-muted mb-2">Phone Number</label>
                <div class="relative">
                    <input type="text" name="phone" placeholder="+380..." class="theme-input w-full p-4 font-bold tracking-wider">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase theme-muted mb-2">Password</label>
                <div class="relative">
                    <input type="password" name="password" placeholder="********" class="theme-input w-full p-4 font-bold tracking-wider">
                </div>
            </div>

            <button class="theme-btn w-full py-4 text-lg shadow-lg mt-4">
                AUTHENTICATE
            </button>
        </form>

        <div class="mt-8 pt-6 border-t theme-border text-center text-xs theme-muted">
            <p>Don't have an account?</p>
            <p class="mt-1">Simply <a href="{{ route('home') }}" class="theme-link font-bold">place your first order</a>.</p>
        </div>
    </div>
</div>
@endsection