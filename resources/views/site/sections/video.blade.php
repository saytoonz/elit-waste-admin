@php use App\Support\Website; $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg); $id = Website::youtubeId($s['youtube_url'] ?? null); @endphp
@if($id)
<section class="{{ Website::bg($bg) }} py-20 sm:py-28">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        @include('site.partials.heading', ['dark' => $dark])
        <div class="reveal aspect-video overflow-hidden rounded-3xl shadow-2xl" x-data="{ play: false }">
            <template x-if="play">
                <iframe class="h-full w-full" src="https://www.youtube-nocookie.com/embed/{{ $id }}?autoplay=1&rel=0" title="Video" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
            </template>
            <button type="button" x-show="!play" @click="play = true" class="group relative h-full w-full" aria-label="Play video">
                <img src="https://i.ytimg.com/vi/{{ $id }}/hqdefault.jpg" alt="" loading="lazy" class="h-full w-full object-cover">
                <span class="absolute inset-0 flex items-center justify-center bg-brand-dark/30">
                    <span class="flex h-20 w-20 items-center justify-center rounded-full bg-white text-brand shadow-2xl transition group-hover:scale-110"><x-site.icon name="play" class="h-8 w-8 translate-x-0.5" /></span>
                </span>
            </button>
        </div>
    </div>
</section>
@endif
