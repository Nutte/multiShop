@extends('tenants.military_gear.layouts.military')
@section('title', 'Establish Comms')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid lg:grid-cols-2 gap-16">
        <div>
            <h1 class="text-4xl md:text-5xl font-bold uppercase text-white mb-8">Establish<br><span class="text-military-accent">Comms</span></h1>
            <p class="text-military-text mb-8 max-w-md">Для связи с операторами используйте зашифрованный канал или посетите нашу базу в Киеве.</p>
            <div class="space-y-8 font-mono">
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-widest mb-2">/// HQ LOCATION</h3>
                    <p class="text-military-text">Kyiv, Ukraine<br>Reitarska St, 21/13<br>Sector B, Floor -1</p>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-widest mb-2">/// FREQUENCY</h3>
                    <p class="text-military-text hover:text-military-accent cursor-pointer">support@karakurt.ua</p>
                    <p class="text-military-text">+380 99 000 00 00</p>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-widest mb-2">/// HOURS</h3>
                    <p class="text-military-text">MON-SUN: 1100 - 2000</p>
                </div>
            </div>
        </div>
        
        <div class="tech-border bg-military-dark p-8">
            <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-military-text uppercase mb-2">Callsign (Name)</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full bg-black/50 border border-military-gray text-white px-4 py-3 focus:border-military-accent focus:outline-none font-mono text-sm transition-colors" placeholder="ENTER YOUR NAME" required>
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-military-text uppercase mb-2">Frequency (Email)</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full bg-black/50 border border-military-gray text-white px-4 py-3 focus:border-military-accent focus:outline-none font-mono text-sm transition-colors" placeholder="ENTER EMAIL ADDRESS" required>
                    @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-military-text uppercase mb-2">Subject</label>
                    <select name="subject" class="w-full bg-black/50 border border-military-gray text-white px-4 py-3 focus:border-military-accent focus:outline-none font-mono text-sm transition-colors">
                        <option>ORDER SUPPORT</option>
                        <option>COLLABORATION</option>
                        <option>GENERAL INQUIRY</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-military-text uppercase mb-2">Transmission (Message)</label>
                    <textarea name="message" rows="4" class="w-full bg-black/50 border border-military-gray text-white px-4 py-3 focus:border-military-accent focus:outline-none font-mono text-sm transition-colors" placeholder="TYPE YOUR MESSAGE..." required>{{ old('message') }}</textarea>
                    @error('message') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="w-full bg-military-accent text-black font-bold uppercase py-4 hover:bg-orange-500 transition-colors">
                    Send Transmission
                </button>
            </form>
        </div>
    </div>
</div>
@endsection