<!-- FILE: resources/views/shop/contact.blade.php -->
@extends('layouts.app')
@section('title', 'Contact Us')

@section('content')
<div class="max-w-4xl mx-auto py-12 px-4">
    
    <div class="text-center mb-12">
        <h1 class="text-4xl font-black uppercase tracking-tighter theme-skew theme-text mb-4">
            Get In Touch
        </h1>
        <p class="theme-muted uppercase tracking-widest text-sm">
            We are here to help you
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
        
        <!-- Info Block -->
        <div class="space-y-8">
            <div class="theme-card p-8 h-full">
                <h2 class="text-xl font-bold uppercase mb-6 theme-text border-b theme-border pb-2">Customer Service</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold uppercase theme-muted mb-1">Email</label>
                        <a href="mailto:support@trishop.local" class="theme-link font-mono text-lg">support@trishop.local</a>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold uppercase theme-muted mb-1">Phone</label>
                        <p class="font-mono text-lg theme-text">+38 (044) 123-45-67</p>
                        <p class="text-xs theme-muted">Mon-Fri: 10:00 - 19:00</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase theme-muted mb-1">Headquarters</label>
                        <p class="theme-text">Kyiv, Khreshchatyk St, 1</p>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t theme-border text-xs theme-muted leading-relaxed">
                    If you have questions about your order, please include your Order # in the message for faster assistance.
                </div>
            </div>
        </div>

        <!-- Form Block -->
        <div>
            <form action="{{ route('contact.store') }}" method="POST" class="theme-card p-8 shadow-xl">
                @csrf
                
                <h2 class="text-xl font-bold uppercase mb-6 theme-text">Send Message</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase mb-1 theme-muted">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" class="theme-input w-full p-3 font-bold text-sm" placeholder="your@email.com" required>
                        @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase mb-1 theme-muted">Phone Number (Optional)</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="theme-input w-full p-3 font-bold text-sm" placeholder="+380...">
                        @error('phone') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase mb-1 theme-muted">Message <span class="text-red-500">*</span></label>
                        <textarea name="message" rows="5" class="theme-input w-full p-3 font-bold text-sm" placeholder="How can we help?" required>{{ old('message') }}</textarea>
                        @error('message') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <button class="theme-btn w-full py-4 text-lg shadow-lg hover:shadow-2xl transition">
                        SEND MESSAGE
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection