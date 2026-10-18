@php use App\Support\Website; $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg); @endphp
<section class="{{ Website::bg($bg) }} py-16 sm:py-24">
    <div class="mx-auto {{ ($s['width'] ?? 'narrow') === 'wide' ? 'max-w-6xl' : 'max-w-3xl' }} px-4 sm:px-6 lg:px-8">
        @if(!empty($s['heading']))
            <h2 class="font-display text-3xl font-extrabold tracking-tight {{ $dark ? 'text-white' : 'text-gray-900' }}">{{ $s['heading'] }}</h2>
        @endif
        <div class="site-prose text-lg {{ $dark ? 'site-prose-invert' : '' }}">{{ Website::markdown($s['body'] ?? '') }}</div>
    </div>
</section>
