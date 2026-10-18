{{-- Title / address / status / SEO fields shared by create and edit --}}
@php $input = 'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6'; @endphp
<div class="space-y-5" x-data="{ title: @js(old('title', $page->title)), slug: @js(old('slug', $page->slug)), touched: {{ $page->exists ? 'true' : 'false' }} }">
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-900">Page title</label>
        <input type="text" name="title" x-model="title" @input="if (!touched) slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')" required class="{{ $input }}">
    </div>
    @unless($page->is_home)
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-900">Web address</label>
            <div class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-primary">
                <span class="flex select-none items-center pl-3 text-sm text-gray-500">{{ preg_replace('#^https?://#', '', url('/')) }}/</span>
                <input type="text" name="slug" x-model="slug" @input="touched = true" class="block flex-1 border-0 bg-transparent py-1.5 pl-0.5 text-gray-900 focus:ring-0 sm:text-sm">
            </div>
        </div>
    @else
        <input type="hidden" name="slug" value="{{ $page->slug }}">
    @endunless
    <div>
        <label class="mb-1 block text-sm font-medium text-gray-900">Status</label>
        <select name="status" class="{{ $input }}" @disabled($page->is_home)>
            <option value="Published" @selected(old('status', $page->status) === 'Published')>Published — visible to everyone</option>
            <option value="Draft" @selected(old('status', $page->status) === 'Draft')>Draft — only visible to you while logged in</option>
        </select>
        @if($page->is_home)
            <input type="hidden" name="status" value="Published">
            <p class="mt-1 text-xs text-gray-500">The homepage is always published.</p>
        @endif
    </div>

    <details class="rounded-md border border-gray-200 p-4" @if($errors->hasAny(['meta_title', 'meta_description'])) open @endif>
        <summary class="cursor-pointer text-sm font-semibold text-gray-900">Search engine & sharing (optional)</summary>
        <div class="mt-4 space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-900">Title in Google results</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" maxlength="150" class="{{ $input }}" placeholder="Defaults to the page title">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-900">Description in Google results</label>
                <textarea name="meta_description" rows="3" maxlength="500" class="{{ $input }}">{{ old('meta_description', $page->meta_description) }}</textarea>
            </div>
            <div x-data="imageInput(@js(old('og_image', $page->og_image)))">
                <label class="mb-1 block text-sm font-medium text-gray-900">Image when shared on WhatsApp / Facebook</label>
                <input type="hidden" name="og_image" :value="value">
                <div class="flex items-center gap-3">
                    <div class="h-16 w-24 overflow-hidden rounded bg-gray-100 ring-1 ring-gray-200"><img x-show="value" :src="url(value)" class="h-full w-full object-cover" alt=""></div>
                    <button type="button" @click="pick()" class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Choose</button>
                    <button type="button" x-show="value" @click="value = null" class="text-sm font-semibold text-red-600">Remove</button>
                </div>
            </div>
        </div>
    </details>
</div>
