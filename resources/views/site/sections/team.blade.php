@php use App\Support\Website; $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg); @endphp
<section class="{{ Website::bg($bg) }} py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @include('site.partials.heading', ['dark' => $dark])
        <ul class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($s['items'] ?? [] as $p)
                @php $img = Website::image($p['image'] ?? null); @endphp
                <li class="reveal text-center">
                    <div class="mx-auto aspect-square w-full max-w-[260px] overflow-hidden rounded-3xl bg-brand/10">
                        @if($img)<img src="{{ $img }}" alt="{{ $p['name'] ?? '' }}" loading="lazy" class="h-full w-full object-cover">@endif
                    </div>
                    <h3 class="mt-5 font-display text-lg font-bold {{ $dark ? 'text-white' : 'text-gray-900' }}">{{ $p['name'] ?? '' }}</h3>
                    <p class="text-sm font-semibold {{ $dark ? 'text-white/70' : 'text-brand' }}">{{ $p['role'] ?? '' }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>
