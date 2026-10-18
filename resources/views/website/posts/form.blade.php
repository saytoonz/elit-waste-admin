@php $input = 'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6'; @endphp
<x-app-layout>
    @section('header')
        Website
    @endsection

    @include('website.partials.nav')

    <form method="POST" action="{{ $post->exists ? route('website.posts.update', $post) : route('website.posts.store') }}"
          x-data="{ title: @js(old('title', $post->title)), slug: @js(old('slug', $post->slug)), touched: {{ $post->exists ? 'true' : 'false' }} }">
        @csrf
        @if($post->exists) @method('PUT') @endif

        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <a href="{{ route('website.posts.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700">&larr; All posts</a>
                <h1 class="mt-1 text-xl font-bold text-gray-900">{{ $post->exists ? 'Edit post' : 'New post' }}</h1>
            </div>
            @if($post->exists)
                <a href="{{ $post->url }}" target="_blank" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">{{ $post->status === 'Draft' ? 'Preview' : 'View post' }} &nearr;</a>
            @endif
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="space-y-5 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-900">Title</label>
                        <input type="text" name="title" x-model="title" @input="if (!touched) slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')" required class="{{ $input }} text-lg font-semibold">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-900">Web address</label>
                        <div class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-primary">
                            <span class="flex select-none items-center pl-3 text-sm text-gray-500">/news/</span>
                            <input type="text" name="slug" x-model="slug" @input="touched = true" class="block flex-1 border-0 bg-transparent py-1.5 pl-0.5 text-gray-900 focus:ring-0 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-900">Summary <span class="font-normal text-gray-500">(shown on cards and at the top of the post)</span></label>
                        <textarea name="excerpt" rows="2" maxlength="500" class="{{ $input }}">{{ old('excerpt', $post->excerpt) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-900">Article</label>
                        <textarea name="body" rows="20" class="{{ $input }} font-mono">{{ old('body', $post->body) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Formatting: <code>## Heading</code>, <code>**bold**</code>, <code>*italic*</code>, <code>- list item</code>, <code>[link text](https://…)</code>, <code>&gt; quote</code>. Leave a blank line between paragraphs.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="space-y-5 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-900">Status</label>
                        <select name="status" class="{{ $input }}">
                            <option value="Published" @selected(old('status', $post->status) === 'Published')>Published</option>
                            <option value="Draft" @selected(old('status', $post->status) === 'Draft')>Draft</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-900">Publish date</label>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}" class="{{ $input }}">
                        <p class="mt-1 text-xs text-gray-500">A future date schedules the post.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-900">Category</label>
                        <input type="text" name="category" value="{{ old('category', $post->category) }}" list="post-categories" class="{{ $input }}" placeholder="e.g. Tips">
                        <datalist id="post-categories">
                            @foreach(\App\Models\Website\WebsitePost::whereNotNull('category')->distinct()->pluck('category') as $cat)<option value="{{ $cat }}">@endforeach
                        </datalist>
                    </div>
                    <div x-data="imageInput(@js(old('cover_image', $post->cover_image)))">
                        <label class="mb-1 block text-sm font-medium text-gray-900">Cover image</label>
                        <input type="hidden" name="cover_image" :value="value">
                        <div class="aspect-[16/10] overflow-hidden rounded-md bg-gray-100 ring-1 ring-gray-200">
                            <img x-show="value" :src="url(value)" class="h-full w-full object-cover" alt="">
                        </div>
                        <div class="mt-2 flex gap-3">
                            <button type="button" @click="pick()" class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" x-text="value ? 'Change image' : 'Choose image'"></button>
                            <button type="button" x-show="value" @click="value = null" class="text-sm font-semibold text-red-600">Remove</button>
                        </div>
                    </div>
                    <button class="w-full rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">Save post</button>
                </div>

                <details class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                    <summary class="cursor-pointer text-sm font-semibold text-gray-900">Search engine (optional)</summary>
                    <div class="mt-4 space-y-4">
                        <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" placeholder="Title in Google results" class="{{ $input }}">
                        <textarea name="meta_description" rows="3" placeholder="Description in Google results" class="{{ $input }}">{{ old('meta_description', $post->meta_description) }}</textarea>
                    </div>
                </details>
            </div>
        </div>
    </form>

    @if($post->exists)
        <form method="POST" action="{{ route('website.posts.destroy', $post) }}" class="mt-8" onsubmit="return confirm('Delete this post?')">
            @csrf @method('DELETE')
            <button class="text-sm font-semibold text-red-600 hover:text-red-500">Delete post</button>
        </form>
    @endif

    @include('website.partials.editor-scripts')
</x-app-layout>
