@php use App\Support\Website; $img = Website::image($s['image'] ?? null); @endphp
<section class="bg-white px-4 py-20 sm:px-6 sm:py-24 lg:px-8">
    <div class="reveal relative isolate mx-auto max-w-7xl overflow-hidden rounded-[2.5rem] bg-brand px-6 py-16 shadow-2xl sm:px-16 sm:py-20">
        @if($img)
            <img src="{{ $img }}" alt="" loading="lazy" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-20 mix-blend-luminosity">
        @endif
        <div class="absolute -right-24 -top-24 -z-10 h-80 w-80 rounded-full bg-brand-accent/40 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-20 -z-10 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="font-display text-3xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $s['heading'] ?? '' }}</h2>
            @if(!empty($s['text']))
                <p class="mx-auto mt-6 max-w-xl text-lg leading-relaxed text-white/85">{{ $s['text'] }}</p>
            @endif
            <div class="mt-10 flex flex-wrap justify-center gap-4">
                @include('site.partials.button', ['label' => $s['cta_label'] ?? null, 'url' => $s['cta_url'] ?? null, 'style' => 'light'])
                @include('site.partials.button', ['label' => $s['secondary_label'] ?? null, 'url' => $s['secondary_url'] ?? null, 'style' => 'outline-light'])
            </div>
        </div>
    </div>
</section>
