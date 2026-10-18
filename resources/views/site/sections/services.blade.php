@php use App\Support\Website; $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg); @endphp
<section class="{{ Website::bg($bg) }} py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @include('site.partials.heading', ['dark' => $dark])
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($s['items'] ?? [] as $item)
                @php $img = Website::image($item['image'] ?? null); @endphp
                <article class="reveal group flex flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-gray-100 shadow-lg shadow-gray-200/40 transition duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    @if($img)
                        <div class="relative aspect-[16/11] overflow-hidden">
                            <img src="{{ $img }}" alt="{{ $item['title'] ?? '' }}" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/50 to-transparent"></div>
                        </div>
                    @endif
                    <div class="flex flex-1 flex-col p-7">
                        <h3 class="font-display text-xl font-bold text-gray-900">{{ $item['title'] ?? '' }}</h3>
                        <p class="mt-3 flex-1 leading-relaxed text-gray-600">{{ $item['text'] ?? '' }}</p>
                        @if(!empty($item['link_label']))
                            <a href="{{ Website::href($item['link_url'] ?? '') }}" class="mt-6 inline-flex items-center gap-2 font-bold text-brand">
                                {{ $item['link_label'] }} <x-site.icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1" />
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
