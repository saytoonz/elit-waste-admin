@php use App\Support\Website; $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg); @endphp
<section class="{{ Website::bg($bg) }} py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @include('site.partials.heading', ['dark' => $dark])
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($s['items'] ?? [] as $t)
                @php $img = Website::image($t['image'] ?? null); @endphp
                <figure class="reveal flex flex-col rounded-3xl bg-white p-8 ring-1 ring-gray-100 shadow-lg shadow-gray-200/40">
                    <svg class="h-10 w-10 text-brand/20" fill="currentColor" viewBox="0 0 32 32" aria-hidden="true"><path d="M9.3 6C4.7 9.1 2 13.5 2 18.3 2 22.6 4.5 26 8.2 26c3 0 5.3-2.4 5.3-5.3 0-2.9-2-5-4.7-5-.5 0-1.2.1-1.4.2.6-3.2 3.5-6.9 6.5-8.6L9.3 6Zm15.4 0c-4.6 3.1-7.3 7.5-7.3 12.3 0 4.3 2.5 7.7 6.2 7.7 3 0 5.3-2.4 5.3-5.3 0-2.9-2-5-4.7-5-.5 0-1.2.1-1.4.2.6-3.2 3.5-6.9 6.5-8.6L24.7 6Z"/></svg>
                    <blockquote class="mt-4 flex-1 text-lg leading-relaxed text-gray-700">{{ $t['quote'] ?? '' }}</blockquote>
                    <figcaption class="mt-8 flex items-center gap-4">
                        @if($img)
                            <img src="{{ $img }}" alt="" class="h-12 w-12 rounded-full object-cover">
                        @else
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand/10 font-display font-bold text-brand">{{ mb_substr($t['name'] ?? '?', 0, 1) }}</span>
                        @endif
                        <div>
                            <div class="font-bold text-gray-900">{{ $t['name'] ?? '' }}</div>
                            <div class="text-sm text-gray-500">{{ $t['role'] ?? '' }}</div>
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
