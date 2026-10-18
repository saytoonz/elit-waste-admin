@php use App\Support\Website; $img = Website::image($s['image'] ?? null); @endphp
<section class="relative isolate overflow-hidden bg-brand-dark">
    @if($img)
        <img src="{{ $img }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-dark/95 via-brand-dark/80 to-brand-dark/50"></div>
    @else
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-dark via-brand-dark to-brand"></div>
    @endif
    <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 sm:py-32 lg:px-8">
        <div class="max-w-3xl">
            @if(!empty($s['eyebrow']))
                <p class="reveal text-sm font-bold uppercase tracking-widest text-brand-accent">{{ $s['eyebrow'] }}</p>
            @endif
            <h1 class="reveal mt-3 font-display text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">{{ $s['heading'] ?? '' }}</h1>
            @if(!empty($s['subheading']))
                <p class="reveal mt-6 max-w-2xl text-lg leading-relaxed text-white/80">{{ $s['subheading'] }}</p>
            @endif
        </div>
    </div>
</section>
