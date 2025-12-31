@extends('tenants.designer_hub.layouts.gadyuka')
@section('title', 'Login')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center p-4">
    <div class="w-full max-w-md border-2 border-white bg-black p-8 relative shadow-[4px_4px_0px_0px_#ffffff]">
        
        <div class="absolute -top-3 -left-3 bg-[#ff003c] text-white font-['JetBrains_Mono'] text-xs px-2 py-1 border border-black">SECURE_LOGIN</div>

        <h1 class="text-4xl font-['Space_Grotesk'] font-bold uppercase text-center mb-2">[IDENTIFY]</h1>
        <p class="font-['JetBrains_Mono'] text-center text-gray-500 mb-8 text-xs uppercase">Scan your retina or enter credentials</p>

        <form action="{{ url('/login') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label class="block font-['JetBrains_Mono'] text-xs text-gray-500 uppercase mb-2">Phone Number</label>
                <div class="relative">
                    <input type="text" name="phone" placeholder="+380..." class="w-full border-b border-gray-600 bg-transparent py-3 font-['JetBrains_Mono'] text-white focus:outline-none focus:border-[#ff003c] placeholder-gray-700 tracking-wider">
                </div>
            </div>

            <div>
                <label class="block font-['JetBrains_Mono'] text-xs text-gray-500 uppercase mb-2">Password</label>
                <div class="relative">
                    <input type="password" name="password" placeholder="********" class="w-full border-b border-gray-600 bg-transparent py-3 font-['JetBrains_Mono'] text-white focus:outline-none focus:border-[#ff003c] placeholder-gray-700 tracking-wider">
                </div>
            </div>

            <button class="w-full bg-white text-black font-['Space_Grotesk'] font-bold py-4 text-lg border-2 border-white hover:bg-[#ff003c] hover:text-white transition-colors uppercase tracking-widest">
                [AUTHENTICATE]
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-700 text-center font-['JetBrains_Mono'] text-xs text-gray-500">
            <p>Don't have an account?</p>
            <p class="mt-1">Simply <a href="{{ route('home') }}" class="text-[#ff003c] hover:text-white font-bold">[PLACE_FIRST_ORDER]</a>.</p>
        </div>
    </div>
</div>
@endsection