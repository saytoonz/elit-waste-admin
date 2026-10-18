{{-- Shared section intro: $s (data), $dark (bool), $center (bool, default true) --}}
@php $center = $center ?? true; $dark = $dark ?? false; @endphp
@if(!empty($s['eyebrow']) || !empty($s['heading']) || !empty($s['subheading']))
    <div class="reveal {{ $center ? 'mx-auto max-w-3xl text-center' : 'max-w-2xl' }} mb-12 lg:mb-16">
        @if(!empty($s['eyebrow']))
            <p class="inline-flex items-center gap-2 rounded-full px-3.5 py-1 text-xs font-bold uppercase tracking-widest {{ $dark ? 'bg-white/10 text-white' : 'bg-brand/10 text-brand' }}">{{ $s['eyebrow'] }}</p>
        @endif
        @if(!empty($s['heading']))
            <h2 class="mt-4 font-display text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-[2.75rem] lg:leading-[1.15] {{ $dark ? 'text-white' : 'text-gray-900' }}">{{ $s['heading'] }}</h2>
        @endif
        @if(!empty($s['subheading']))
            <p class="mt-5 text-lg leading-relaxed {{ $dark ? 'text-white/75' : 'text-gray-600' }}">{{ $s['subheading'] }}</p>
        @endif
    </div>
@endif
