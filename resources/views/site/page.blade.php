@extends('site.layout')

@section('title', $page->meta_title ?: ($page->is_home ? '' : $page->title))
@section('description', $page->meta_description ?? '')
@section('og_image', \App\Support\Website::image($page->og_image) ?? '')
@if($page->status !== 'Published')
    @section('preview_notice', 'Draft preview — this page is not visible to the public yet.')
@endif

@section('content')
    @forelse($sections as $section)
        <div id="section-{{ $section->id }}" class="scroll-mt-20">
            @includeIf('site.sections.' . $section->type, ['s' => $section->data ?? [], 'section' => $section])
        </div>
    @empty
        <div class="mx-auto max-w-3xl px-4 py-32 text-center">
            <h1 class="font-display text-4xl font-extrabold">{{ $page->title }}</h1>
            <p class="mt-4 text-gray-500">This page has no content yet.</p>
        </div>
    @endforelse
@endsection
