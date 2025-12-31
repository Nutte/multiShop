<!-- FILE: resources/views/tenants/street_style/auth/login.blade.php -->
@extends('tenants.street_style.layouts.artefact')
@section('title', 'Login - ARTEFACT.ROOM')

@section('content')
<main class="w-full min-h-[80vh] flex items-center justify-center pt-20 pb-20 relative">
    <div class="paper-block p-10 max-w-md w-full mx-6 rotate-[-1deg]">
        <div class="text-center mb-8">
            <h2 class="font-display font-bold text-3xl uppercase">Identification</h2>
        </div>
        <form action="{{ route('client.login') }}" method="POST" class="space-y-8">
            @csrf
            <input type="text" name="phone" placeholder="PHONE (+380...)" class="input-sketch" required>
            <input type="password" name="password" placeholder="PASSWORD" class="input-sketch" required>
            <button type="submit" class="w-full border-2 border-black py-3 font-bold font-tech text-sm uppercase hover:bg-black hover:text-white transition">
                Authorize Access
            </button>
        </form>
        <div class="mt-8 text-center text-sm text-gray-600">
            <p>Don't have an account? Your access will be created with your first order.</p>
        </div>
    </div>
</main>
@endsection