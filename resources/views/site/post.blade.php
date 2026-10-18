@extends('site.layout')

@php use App\Support\Website; $cover = Website::image($post->cover_image); @endphp

@section('title', $post->meta_title ?: $post->title)
@section('description', $post->meta_description ?: ($post->excerpt ?? ''))
@section('og_image', $cover ?? '')
@if(!$isLive)
    @section('preview_notice', 'Draft preview — this post is not visible to the public yet.')
@endif

@section('content')
    <article>
        <header class="relative isolate overflow-hidden bg-brand-dark">
            @if($cover)
                <img src="{{ $cover }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-35">
            @endif
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-brand-dark via-brand-dark/80 to-brand-dark/40"></div>
            <div class="mx-auto max-w-3xl px-4 py-24 text-center sm:px-6 sm:py-32">
                <a href="{{ route('site.page', 'news') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-white/70 hover:text-white">
                    <x-site.icon name="chevron-left" class="h-4 w-4" /> All news
                </a>
                @if($post->category)
                    <p class="mt-6 text-sm font-bold uppercase tracking-widest text-brand-accent">{{ $post->category }}</p>
                @endif
                <h1 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $post->title }}</h1>
                <p class="mt-6 text-white/70">
                    {{ optional($post->published_at ?? $post->created_at)->format('F j, Y') }}
                    @if($post->author) · {{ $post->author->name }} @endif
                </p>
            </div>
        </header>

        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 sm:py-20">
            @if($post->excerpt)
                <p class="text-xl leading-relaxed text-gray-700">{{ $post->excerpt }}</p>
            @endif
            <div class="site-prose mt-8 text-lg">{{ Website::markdown($post->body) }}</div>

            <div class="mt-14 flex flex-wrap items-center gap-3 border-t pt-8">
                <span class="text-sm font-semibold text-gray-500">Share:</span>
                @php $shareUrl = urlencode($post->url); $shareText = urlencode($post->title); @endphp
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-700 hover:bg-brand hover:text-white" aria-label="Share on Facebook"><x-site.social name="facebook" /></a>
                <a href="https://x.com/intent/post?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener" class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-700 hover:bg-brand hover:text-white" aria-label="Share on X"><x-site.social name="x" class="h-4 w-4" /></a>
                <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener" class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-700 hover:bg-[#25D366] hover:text-white" aria-label="Share on WhatsApp"><x-site.social name="whatsapp" /></a>
            </div>
        </div>
    </article>

    @if($related->isNotEmpty())
        <section class="bg-brand/5 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="mb-10 font-display text-3xl font-extrabold text-gray-900">More from our blog</h2>
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($related as $item)
                        @include('site.partials.post-card', ['post' => $item])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
