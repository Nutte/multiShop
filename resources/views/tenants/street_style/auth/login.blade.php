<!-- FILE: resources/views/tenants/street_style/auth/login.blade.php -->
@extends('layouts.app')
@section('title', 'Login')
@section('body_class', 'bg-black text-white font-sans')
@section('nav_class', 'bg-black border-b border-yellow-400')
@section('brand_name', 'STREET STYLE')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="w-full max-w-md bg-gray-900 p-8 border-2 border-yellow-400 shadow-[8px_8px_0px_0px_rgba(250,204,21,1)]">
        <h1 class="text-4xl font-black italic uppercase mb-6 text-center transform -skew-x-6">
            Member Area
        </h1>

        <form action="{{ url('/login') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-xs font-bold uppercase text-yellow-400 mb-2">Phone Number</label>
                <input type="text" name="phone" placeholder="+380..." class="w-full bg-black border-2 border-gray-700 p-3 text-white focus:border-white focus:outline-none font-mono font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-yellow-400 mb-2">Password</label>
                <input type="password" name="password" placeholder="********" class="w-full bg-black border-2 border-gray-700 p-3 text-white focus:border-white focus:outline-none">
            </div>

            <button class="w-full bg-yellow-400 text-black font-black uppercase py-4 text-xl hover:bg-white transition transform hover:-translate-y-1">
                Enter Base
            </button>
        </form>

        <div class="mt-8 text-center text-xs text-gray-500">
            <p>New here? Just make an order to get an account.</p>
        </div>
    </div>
</div>
@endsection