@extends('tenants.designer_hub.layouts.gadyuka')
@section('title', 'Contact Us')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    
    <div class="text-center mb-12 border-b border-gray-800 pb-8">
        <h1 class="text-4xl md:text-6xl font-['Space_Grotesk'] font-black uppercase tracking-tighter mb-4">
            [GET_IN_TOUCH]
        </h1>
        <p class="font-['JetBrains_Mono'] text-gray-500 uppercase tracking-widest text-sm">
            We are here to help you // お手伝いします
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        
        <!-- Info Block -->
        <div class="space-y-8">
            <div class="border-2 border-white bg-black p-8 h-full relative">
                <div class="absolute -top-3 left-4 bg-black px-2 font-['JetBrains_Mono'] text-xs text-[#ff003c] border border-white">CONTACT_DATA</div>
                
                <h2 class="text-xl font-['Space_Grotesk'] font-bold uppercase mb-6 border-b border-gray-700 pb-2">Customer Service</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block font-['JetBrains_Mono'] text-xs text-gray-500 uppercase mb-1">Email</label>
                        <a href="mailto:support@trishop.local" class="font-['JetBrains_Mono'] text-lg hover:text-[#ff003c] transition-colors">support@trishop.local</a>
                    </div>
                    
                    <div>
                        <label class="block font-['JetBrains_Mono'] text-xs text-gray-500 uppercase mb-1">Phone</label>
                        <p class="font-['JetBrains_Mono'] text-lg">+38 (044) 123-45-67</p>
                        <p class="font-['JetBrains_Mono'] text-xs text-gray-500 mt-1">Mon-Fri: 10:00 - 19:00</p>
                    </div>

                    <div>
                        <label class="block font-['JetBrains_Mono'] text-xs text-gray-500 uppercase mb-1">Headquarters</label>
                        <p class="font-['JetBrains_Mono']">Kyiv, Khreshchatyk St, 1</p>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-700 font-['JetBrains_Mono'] text-xs text-gray-500 leading-relaxed">
                    If you have questions about your order, please include your Order # in the message for faster assistance.
                </div>
            </div>
        </div>

        <!-- Form Block -->
        <div>
            <form action="{{ route('contact.store') }}" method="POST" class="border-2 border-white bg-black p-8 shadow-[4px_4px_0px_0px_#ffffff] relative">
                <div class="absolute -top-3 -left-3 bg-[#ff003c] text-white font-['JetBrains_Mono'] text-xs px-2 py-1 border border-black">SEND_MESSAGE</div>
                
                <h2 class="text-xl font-['Space_Grotesk'] font-bold uppercase mb-6">[TRANSMIT_MESSAGE]</h2>

                <div class="space-y-6">
                    <div>
                        <label class="block font-['JetBrains_Mono'] text-xs text-gray-500 uppercase mb-1">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border-b border-gray-600 bg-transparent py-3 font-['JetBrains_Mono'] text-white focus:outline-none focus:border-[#ff003c] placeholder-gray-700" placeholder="your@email.com" required>
                        @error('email') <span class="font-['JetBrains_Mono'] text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-['JetBrains_Mono'] text-xs text-gray-500 uppercase mb-1">Phone Number (Optional)</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border-b border-gray-600 bg-transparent py-3 font-['JetBrains_Mono'] text-white focus:outline-none focus:border-[#ff003c] placeholder-gray-700" placeholder="+380...">
                        @error('phone') <span class="font-['JetBrains_Mono'] text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-['JetBrains_Mono'] text-xs text-gray-500 uppercase mb-1">Message <span class="text-red-500">*</span></label>
                        <textarea name="message" rows="5" class="w-full border border-gray-600 bg-transparent p-3 font-['JetBrains_Mono'] text-white focus:outline-none focus:border-[#ff003c] placeholder-gray-700" placeholder="How can we help?" required>{{ old('message') }}</textarea>
                        @error('message') <span class="font-['JetBrains_Mono'] text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-8">
                    <button class="w-full bg-white text-black font-['Space_Grotesk'] font-bold py-4 text-lg border-2 border-white hover:bg-[#ff003c] hover:text-white transition-colors uppercase tracking-widest">
                        [TRANSMIT]
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection