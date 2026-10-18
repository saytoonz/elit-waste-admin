@php
    use App\Support\Website;
    $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg);
    $count = max(1, min(48, (int) ($s['count'] ?? 3)));
    $query = \App\Models\Website\WebsitePost::published()->latest('published_at');
    $posts = ($s['paginate'] ?? 'no') === 'yes' ? $query->paginate($count)->withQueryString() : $query->take($count)->get();
@endphp
@if($posts->count())
<section class="{{ Website::bg($bg) }} py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @include('site.partials.heading', ['dark' => $dark])
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($posts as $post)
                @include('site.partials.post-card', ['post' => $post])
            @endforeach
        </div>
        @if($posts instanceof \Illuminate\Contracts\Pagination\Paginator && $posts->hasPages())
            <div class="mt-12">{{ $posts->links() }}</div>
        @endif
    </div>
</section>
@endif
