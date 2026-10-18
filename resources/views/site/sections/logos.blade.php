@php use App\Support\Website; $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg); @endphp
<section class="{{ Website::bg($bg) }} py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if(!empty($s['heading']))
            <h2 class="text-center text-sm font-bold uppercase tracking-widest {{ $dark ? 'text-white/60' : 'text-gray-500' }}">{{ $s['heading'] }}</h2>
        @endif
        <div class="mt-10 flex flex-wrap items-center justify-center gap-x-12 gap-y-8">
            @foreach($s['items'] ?? [] as $logo)
                @php $img = Website::image($logo['image'] ?? null); @endphp
                @if($img)
                    @if(!empty($logo['url']))<a href="{{ $logo['url'] }}" target="_blank" rel="noopener">@endif
                    <img src="{{ $img }}" alt="{{ $logo['name'] ?? '' }}" loading="lazy" class="h-12 w-auto max-w-[160px] object-contain opacity-70 grayscale transition hover:opacity-100 hover:grayscale-0">
                    @if(!empty($logo['url']))</a>@endif
                @endif
            @endforeach
        </div>
    </div>
</section>
