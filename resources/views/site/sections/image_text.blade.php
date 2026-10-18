@php
    use App\Support\Website;
    $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg);
    $img = Website::image($s['image'] ?? null);
    $left = ($s['image_position'] ?? 'right') === 'left';
@endphp
<section class="{{ Website::bg($bg) }} py-20 sm:py-28 overflow-hidden">
    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:gap-20 lg:px-8">
        @if($img)
            <div class="reveal relative {{ $left ? 'lg:order-first' : 'lg:order-last' }}">
                <div class="absolute -inset-4 -z-0 rounded-[2.5rem] {{ $left ? '-rotate-2' : 'rotate-2' }} bg-brand/10"></div>
                <img src="{{ $img }}" alt="{{ $s['heading'] ?? '' }}" loading="lazy" class="relative aspect-[4/3] w-full rounded-[2rem] object-cover shadow-2xl">
                <div class="absolute -bottom-6 {{ $left ? '-right-4 sm:-right-6' : '-left-4 sm:-left-6' }} hidden sm:flex h-24 w-24 items-center justify-center rounded-3xl bg-brand-accent text-white shadow-xl">
                    <x-site.icon name="recycle" class="h-10 w-10" />
                </div>
            </div>
        @endif
        <div class="reveal {{ $img ? '' : 'lg:col-span-2 max-w-3xl' }}">
            @if(!empty($s['eyebrow']))
                <p class="text-sm font-bold uppercase tracking-widest {{ $dark ? 'text-brand-accent' : 'text-brand' }}">{{ $s['eyebrow'] }}</p>
            @endif
            @if(!empty($s['heading']))
                <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl {{ $dark ? 'text-white' : 'text-gray-900' }}">{{ $s['heading'] }}</h2>
            @endif
            <div class="mt-6 text-lg site-prose {{ $dark ? 'site-prose-invert' : '' }}">{{ Website::markdown($s['body'] ?? '') }}</div>
            @if(!empty($s['bullets']))
                <ul class="mt-8 space-y-4">
                    @foreach($s['bullets'] as $b)
                        @if(!empty($b['text']))
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand text-white"><x-site.icon name="check" class="h-3.5 w-3.5" /></span>
                                <span class="font-semibold {{ $dark ? 'text-white' : 'text-gray-800' }}">{{ $b['text'] }}</span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
            @if(!empty($s['cta_label']))
                <div class="mt-10">@include('site.partials.button', ['label' => $s['cta_label'], 'url' => $s['cta_url'] ?? null, 'style' => $dark ? 'light' : 'primary'])</div>
            @endif
        </div>
    </div>
</section>
