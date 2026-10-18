@php use App\Support\Website; $img = Website::image($s['image'] ?? null); @endphp
<section class="relative isolate overflow-hidden bg-brand-dark">
    @if($img)
        <img src="{{ $img }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover object-[70%_center]" fetchpriority="high">
    @endif
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-brand-dark via-brand-dark/85 to-brand-dark/30"></div>
    <div class="absolute -left-40 -top-40 -z-10 h-[480px] w-[480px] rounded-full bg-brand/40 blur-3xl"></div>

    <div class="mx-auto flex min-h-[640px] max-w-7xl items-center px-4 py-24 sm:px-6 lg:min-h-[760px] lg:px-8">
        <div class="max-w-2xl">
            @if(!empty($s['eyebrow']))
                <p class="reveal inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-brand-accent"></span>{{ $s['eyebrow'] }}
                </p>
            @endif
            @if(!empty($s['heading']))
                <h1 class="reveal mt-6 font-display text-4xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-6xl lg:text-7xl">{{ $s['heading'] }}</h1>
            @endif
            @if(!empty($s['subheading']))
                <p class="reveal mt-6 max-w-xl text-lg leading-relaxed text-white/80 sm:text-xl">{{ $s['subheading'] }}</p>
            @endif
            <div class="reveal mt-10 flex flex-wrap gap-4">
                @include('site.partials.button', ['label' => $s['cta_label'] ?? null, 'url' => $s['cta_url'] ?? null, 'style' => 'accent'])
                @include('site.partials.button', ['label' => $s['secondary_label'] ?? null, 'url' => $s['secondary_url'] ?? null, 'style' => 'outline-light'])
            </div>
            @if(!empty($s['badges']))
                <ul class="reveal mt-10 flex flex-wrap gap-x-6 gap-y-3">
                    @foreach($s['badges'] as $badge)
                        @if(!empty($badge['text']))
                            <li class="flex items-center gap-2 text-sm font-semibold text-white/90">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-accent/90"><x-site.icon name="check" class="h-3.5 w-3.5 text-white" /></span>
                                {{ $badge['text'] }}
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</section>
