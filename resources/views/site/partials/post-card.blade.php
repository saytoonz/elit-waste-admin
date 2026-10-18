@php $cover = \App\Support\Website::image($post->cover_image); @endphp
<article class="reveal group flex flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-gray-100 shadow-lg shadow-gray-200/40 transition duration-300 hover:-translate-y-1 hover:shadow-2xl">
    <a href="{{ $post->url }}" class="relative block aspect-[16/10] overflow-hidden bg-brand/10">
        @if($cover)<img src="{{ $cover }}" alt="{{ $post->title }}" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">@endif
        @if($post->category)
            <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-bold text-brand">{{ $post->category }}</span>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-7">
        <time class="text-sm font-semibold text-gray-400" datetime="{{ optional($post->published_at)->toDateString() }}">{{ optional($post->published_at ?? $post->created_at)->format('M j, Y') }}</time>
        <h3 class="mt-2 font-display text-xl font-bold leading-snug text-gray-900"><a href="{{ $post->url }}" class="hover:text-brand">{{ $post->title }}</a></h3>
        @if($post->excerpt)<p class="mt-3 flex-1 leading-relaxed text-gray-600">{{ $post->excerpt }}</p>@endif
        <a href="{{ $post->url }}" class="mt-6 inline-flex items-center gap-2 font-bold text-brand">Read more <x-site.icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1" /></a>
    </div>
</article>
