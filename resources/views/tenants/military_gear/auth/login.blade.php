<!-- FILE: resources/views/tenants/military_gear/auth/login.blade.php -->
@extends('layouts.app')
@section('title', 'Access Control')
@section('body_class', 'bg-stone-900 text-stone-100 font-mono')
@section('nav_class', 'bg-stone-800 border-b-4 border-orange-700')
@section('brand_name', 'MILITARY GEAR [TAC-OPS]')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="w-full max-w-md bg-stone-800 p-1 border border-stone-600">
        <div class="bg-stone-900 p-8 border border-stone-700">
            <div class="flex items-center justify-between mb-8 border-b border-stone-700 pb-4">
                <h1 class="text-xl font-bold text-orange-600">ACCESS CONTROL</h1>
                <div class="w-3 h-3 bg-red-600 rounded-full animate-pulse"></div>
            </div>

            <form action="{{ url('/login') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-stone-500 mb-1">USER ID (PHONE)</label>
                    <div class="flex">
                        <span class="bg-stone-800 border border-r-0 border-stone-600 p-2 text-stone-500 text-sm flex items-center">></span>
                        <input type="text" name="phone" placeholder="+380..." class="w-full bg-stone-900 border border-stone-600 p-2 text-white focus:border-orange-600 focus:outline-none placeholder-stone-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-500 mb-1">ACCESS KEY (PASSWORD)</label>
                    <div class="flex">
                        <span class="bg-stone-800 border border-r-0 border-stone-600 p-2 text-stone-500 text-sm flex items-center">#</span>
                        <input type="password" name="password" placeholder="********" class="w-full bg-stone-900 border border-stone-600 p-2 text-white focus:border-orange-600 focus:outline-none placeholder-stone-700">
                    </div>
                </div>

                <div class="pt-4">
                    <button class="w-full bg-orange-700 text-white font-bold py-3 text-sm hover:bg-orange-600 transition flex justify-center items-center gap-2">
                        <span>AUTHENTICATE</span>
                        <span class="text-xs">[ENTER]</span>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-4 border-t border-stone-800 text-center text-[10px] text-stone-600">
                UNAUTHORIZED ACCESS IS PROHIBITED. <br>
                SECURE CONNECTION ESTABLISHED.
            </div>
        </div>
    </div>
</div>
@endsection