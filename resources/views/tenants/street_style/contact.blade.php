<!-- FILE: resources/views/tenants/street_style/contact.blade.php -->
@extends('tenants.street_style.layouts.artefact')
@section('title', 'Contact - ARTEFACT.ROOM')

@section('content')
<main class="w-full pt-24 pb-20">
    <!-- CAUTION TAPE DIVIDER -->
    <div class="caution-tape py-3 border-y-4 border-black mb-12">
        <div class="caution-scroll">
            CONTACT SUPPORT // GET IN TOUCH // CUSTOMER SERVICE // ARTEFACT.ROOM // CONTACT SUPPORT // GET IN TOUCH // CUSTOMER SERVICE // ARTEFACT.ROOM //
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6">
        <!-- Section Title -->
        <div class="text-center mb-16 relative">
            <h1 class="text-6xl md:text-8xl font-display font-black text-white uppercase italic mb-4">
                Contact <span class="text-[#ccff00] font-spray not-italic">Us</span>
            </h1>
            <div class="absolute -top-10 right-10 transform rotate-12 font-spray text-4xl text-pink-500 opacity-80">
                Hello!
            </div>
            <p class="font-tech text-gray-400 uppercase tracking-widest text-sm">
                We are here to help you
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
            
            <!-- Info Block -->
            <div class="space-y-8">
                <div class="paper-block p-8 h-full relative">
                    <div class="tape-strip -top-3 left-1/2 -translate-x-1/2 w-32 bg-white/50 rotate-[-2deg]"></div>
                    <h2 class="text-2xl font-display uppercase mb-6 text-black border-b border-black pb-2">Customer Service</h2>
                    
                    <div class="space-y-8">
                        <div>
                            <label class="block text-xs font-tech uppercase text-gray-600 mb-2">Email</label>
                            <a href="mailto:hello@artefact.ua" class="font-mono text-lg text-black hover:text-pink-600 transition">
                                hello@artefact.ua
                            </a>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-tech uppercase text-gray-600 mb-2">Phone</label>
                            <p class="font-mono text-lg text-black">+38 (044) 123-45-67</p>
                            <p class="text-xs text-gray-500 mt-1">Mon-Fri: 10:00 - 19:00</p>
                        </div>

                        <div>
                            <label class="block text-xs font-tech uppercase text-gray-600 mb-2">Headquarters</label>
                            <p class="text-black">Kyiv, Khreshchatyk St, 1</p>
                            <p class="text-xs text-gray-500 mt-1">Visit by appointment only</p>
                        </div>
                    </div>

                    <div class="mt-12 pt-6 border-t border-gray-300 text-xs text-gray-500 leading-relaxed">
                        <div class="flex items-start gap-2">
                            <span class="text-lg leading-none">&uarr;</span>
                            <span>If you have questions about your order, please include your Order # in the message for faster assistance.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Block -->
            <div>
                <div class="paper-block p-8 relative">
                    <div class="tag-sticker top-4 right-4 bg-[#ccff00] text-black">Urgent!</div>
                    <h2 class="text-2xl font-display uppercase mb-6 text-black">Send Message</h2>

                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        @if(auth()->check())
                        <!-- Auto-fill for logged in users -->
                        <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                        <input type="hidden" name="phone" value="{{ auth()->user()->phone }}">
                        @endif

                        <div>
                            <label class="block text-xs font-tech uppercase mb-2 text-gray-600">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" 
                                   value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}" 
                                   class="w-full p-3 bg-transparent border-b-2 border-black text-black font-tech text-sm placeholder-gray-500" 
                                   placeholder="your@email.com" 
                                   {{ auth()->check() ? 'readonly' : 'required' }}>
                            @error('email') 
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-tech uppercase mb-2 text-gray-600">Phone Number</label>
                            <input type="text" name="phone" 
                                   value="{{ old('phone', auth()->check() ? auth()->user()->phone : '') }}" 
                                   class="w-full p-3 bg-transparent border-b-2 border-black text-black font-tech text-sm placeholder-gray-500" 
                                   placeholder="+380..."
                                   {{ auth()->check() ? 'readonly' : '' }}>
                            @error('phone') 
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-tech uppercase mb-2 text-gray-600">
                                Message <span class="text-red-500">*</span>
                            </label>
                            <textarea name="message" rows="5" 
                                      class="w-full p-3 bg-transparent border-2 border-black text-black font-tech text-sm placeholder-gray-500" 
                                      placeholder="How can we help?" 
                                      required>{{ old('message') }}</textarea>
                            @error('message') 
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        <div class="mt-8">
                            <button class="w-full bg-black text-white font-display font-black text-xl uppercase py-4 
                                           hover:bg-pink-500 transition transform hover:scale-[1.02] 
                                           shadow-[6px_6px_0px_#ccff00]">
                                SEND MESSAGE
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-6 pt-4 border-t border-gray-300">
                        <p class="text-xs text-gray-500 text-center">
                            We typically respond within 24 hours on business days
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map/Info Section -->
        <div class="mt-16">
            <div class="paper-block p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div class="p-4">
                        <div class="text-3xl mb-2">🚚</div>
                        <h4 class="font-display uppercase text-black mb-2">Shipping</h4>
                        <p class="text-sm text-gray-600">Nova Poshta delivery across Ukraine. 1-3 business days.</p>
                    </div>
                    <div class="p-4">
                        <div class="text-3xl mb-2">🔄</div>
                        <h4 class="font-display uppercase text-black mb-2">Returns</h4>
                        <p class="text-sm text-gray-600">14-day return policy. Items must be unworn with tags.</p>
                    </div>
                    <div class="p-4">
                        <div class="text-3xl mb-2">🛡️</div>
                        <h4 class="font-display uppercase text-black mb-2">Security</h4>
                        <p class="text-sm text-gray-600">Secure payment processing. SSL encrypted checkout.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Graffiti Background Elements -->
<div class="fixed inset-0 pointer-events-none opacity-10 z-0">
    <div class="absolute top-1/4 left-5 font-spray text-[8rem] text-white rotate-[-15deg]">CONTACT</div>
    <div class="absolute bottom-1/4 right-5 font-spray text-[6rem] text-white rotate-[10deg]">HELP</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-expand textarea
        const textarea = document.querySelector('textarea[name="message"]');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
            
            // Trigger once on load
            setTimeout(() => {
                textarea.dispatchEvent(new Event('input'));
            }, 100);
        }
    });
</script>
@endsection