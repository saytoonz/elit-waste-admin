@php use App\Support\Website; $img = Website::image($s['image'] ?? null); $items = array_filter($s['items'] ?? [], fn ($i) => !empty($i['value'])); @endphp
<section class="relative isolate overflow-hidden bg-brand-dark py-24 sm:py-32">
    @if($img)
        <img src="{{ $img }}" alt="" loading="lazy" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-40">
    @endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-dark/70 via-brand-dark/80 to-brand-dark"></div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal mx-auto max-w-3xl text-center">
            @if(!empty($s['eyebrow']))
                <p class="text-sm font-bold uppercase tracking-widest text-brand-accent">{{ $s['eyebrow'] }}</p>
            @endif
            <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $s['heading'] ?? '' }}</h2>
            @if(!empty($s['body']))
                <p class="mt-6 text-lg leading-relaxed text-white/80">{{ $s['body'] }}</p>
            @endif
        </div>
        @if($items)
            <dl class="mt-16 grid gap-px overflow-hidden rounded-3xl bg-white/10 ring-1 ring-white/10 {{ [1 => '', 2 => 'sm:grid-cols-2', 3 => 'sm:grid-cols-3'][count($items)] ?? 'sm:grid-cols-2 lg:grid-cols-4' }}">
                @foreach($items as $item)
                    <div class="reveal bg-brand-dark/60 p-8 text-center backdrop-blur">
                        <dd class="font-display text-4xl font-extrabold text-white sm:text-5xl">{{ Website::fill($item['value']) }}</dd>
                        <dt class="mt-2 text-sm font-semibold text-white/70">{{ $item['label'] ?? '' }}</dt>
                    </div>
                @endforeach
            </dl>
        @endif
        @if(!empty($s['cta_label']))
            <div class="reveal mt-12 text-center">@include('site.partials.button', ['label' => $s['cta_label'], 'url' => $s['cta_url'] ?? null, 'style' => 'accent'])</div>
        @endif
    </div>
</section>
