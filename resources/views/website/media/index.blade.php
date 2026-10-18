<x-app-layout>
    @section('header')
        Website
    @endsection

    @include('website.partials.nav')

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Media library</h1>
            <p class="mt-2 text-sm text-gray-700">All website images. Upload here or straight from any image field.</p>
        </div>
        <form method="POST" action="{{ route('website.media.store') }}" enctype="multipart/form-data" class="mt-4 sm:ml-16 sm:mt-0" x-data>
            @csrf
            <label class="block cursor-pointer rounded-md bg-primary px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-secondary">
                Upload images
                <input type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" @change="$el.form.submit()">
            </label>
        </form>
    </div>

    <form method="GET" class="mt-6">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search images…" class="rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-primary">
    </form>

    <ul class="mt-6 grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
        @forelse($media as $item)
            <li class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-900/5" x-data="{ editing: false, copied: false }">
                <a href="{{ $item->url }}" target="_blank" class="block aspect-square bg-gray-100">
                    <img src="{{ $item->url }}" alt="{{ $item->alt }}" loading="lazy" class="h-full w-full object-cover">
                </a>
                <div class="p-3">
                    <p class="truncate text-xs font-semibold text-gray-900" title="{{ $item->original_name }}">{{ $item->original_name }}</p>
                    <p class="text-xs text-gray-500">
                        @if($item->width){{ $item->width }}×{{ $item->height }} · @endif{{ number_format($item->size / 1024) }} KB
                    </p>
                    <div class="mt-2 flex items-center gap-3 text-xs font-semibold">
                        <button type="button" @click="editing = !editing" class="text-primary">Details</button>
                        <button type="button" @click="navigator.clipboard.writeText(@js($item->url)); copied = true; setTimeout(() => copied = false, 1500)" class="text-gray-600" x-text="copied ? 'Copied!' : 'Copy link'"></button>
                    </div>
                    <div x-show="editing" x-cloak class="mt-3 space-y-2">
                        <form method="POST" action="{{ route('website.media.update', $item) }}">
                            @csrf @method('PATCH')
                            <label class="text-xs text-gray-600">Description (for accessibility &amp; Google)</label>
                            <input type="text" name="alt" value="{{ $item->alt }}" class="mt-1 block w-full rounded border-0 py-1 text-xs ring-1 ring-inset ring-gray-300 focus:ring-primary">
                            <button class="mt-2 rounded bg-primary px-2 py-1 text-xs font-semibold text-white">Save</button>
                        </form>
                        <form method="POST" action="{{ route('website.media.destroy', $item) }}" onsubmit="return confirm('Delete this image? Sections using it will show no image.')">
                            @csrf @method('DELETE')
                            <button class="text-xs font-semibold text-red-600">Delete image</button>
                        </form>
                    </div>
                </div>
            </li>
        @empty
            <li class="col-span-full py-12 text-center text-sm text-gray-500">No images found.</li>
        @endforelse
    </ul>
    <div class="mt-6">{{ $media->links() }}</div>
</x-app-layout>
