@php
    use App\Support\Website;
    $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg);
    $photos = collect($s['items'] ?? [])->filter(fn ($i) => !empty($i['image']))
        ->map(fn ($i) => ['src' => Website::image($i['image']), 'caption' => $i['caption'] ?? ''])->values();
@endphp
@if($photos->isNotEmpty())
<section class="{{ Website::bg($bg) }} py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @include('site.partials.heading', ['dark' => $dark])
        <div class="columns-1 gap-5 sm:columns-2 lg:columns-3 [&>*]:mb-5">
            @foreach($photos as $i => $photo)
                <button type="button" class="reveal group relative block w-full overflow-hidden rounded-3xl break-inside-avoid focus:outline-none focus-visible:ring-4 focus-visible:ring-brand"
                        @click="$dispatch('open-lightbox', { items: @js($photos), index: {{ $i }} })">
                    <img src="{{ $photo['src'] }}" alt="{{ $photo['caption'] }}" loading="lazy"
                         class="w-full object-cover transition duration-700 group-hover:scale-105 {{ ['aspect-[4/5]', 'aspect-[4/3]', 'aspect-square'][$i % 3] }}">
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/70 via-transparent to-transparent opacity-0 transition group-hover:opacity-100"></div>
                    @if($photo['caption'])
                        <span class="absolute bottom-4 left-4 right-4 translate-y-2 text-left font-semibold text-white opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100">{{ $photo['caption'] }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
</section>
@endif
