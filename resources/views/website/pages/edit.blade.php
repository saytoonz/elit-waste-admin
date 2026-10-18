<x-app-layout>
    @section('header')
        Website
    @endsection

    @include('website.partials.nav')

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('website.pages.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700">&larr; All pages</a>
            <h1 class="mt-1 text-xl font-bold text-gray-900">
                {{ $page->title }}
                @if($page->is_home)<span class="ml-2 align-middle rounded-md bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary">Homepage</span>@endif
                @if($page->status === 'Draft')<span class="ml-2 align-middle rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">Draft</span>@endif
            </h1>
        </div>
        <a href="{{ $page->url }}" target="_blank" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            {{ $page->status === 'Draft' ? 'Preview page' : 'View page' }} &nearr;
        </a>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        {{-- Sections --}}
        <div class="lg:col-span-2" x-data="{ adding: false }">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900">Sections <span class="font-normal text-gray-500">— top to bottom, as they appear on the page</span></h2>
            </div>

            <ol class="space-y-3">
                @forelse($page->sections as $i => $section)
                    @php $def = $section->definition(); @endphp
                    <li class="flex items-center gap-4 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-900/5 {{ $section->is_visible ? '' : 'opacity-60' }}">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary/10 text-sm font-bold text-primary">{{ $i + 1 }}</span>
                        <a href="{{ route('website.sections.edit', $section) }}" class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900">{{ $def['label'] }}</span>
                                @unless($section->is_visible)<span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600">Hidden</span>@endunless
                            </div>
                            <p class="truncate text-sm text-gray-500">{{ $section->summary() ?: '—' }}</p>
                        </a>
                        <div class="flex shrink-0 items-center gap-1">
                            <form method="POST" action="{{ route('website.sections.move', $section) }}">@csrf<input type="hidden" name="direction" value="up">
                                <button class="rounded p-1.5 text-gray-500 hover:bg-gray-100 disabled:opacity-30" title="Move up" @disabled($loop->first)>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('website.sections.move', $section) }}">@csrf<input type="hidden" name="direction" value="down">
                                <button class="rounded p-1.5 text-gray-500 hover:bg-gray-100 disabled:opacity-30" title="Move down" @disabled($loop->last)>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                </button>
                            </form>
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button type="button" @click="open = !open" class="rounded p-1.5 text-gray-500 hover:bg-gray-100" title="More">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm0 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm0 5.5a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z"/></svg>
                                </button>
                                <div x-show="open" x-cloak class="absolute right-0 z-10 mt-1 w-40 rounded-md bg-white py-1 shadow-lg ring-1 ring-gray-900/5">
                                    <a href="{{ route('website.sections.edit', $section) }}" class="block px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">Edit</a>
                                    <form method="POST" action="{{ route('website.sections.toggle', $section) }}">@csrf
                                        <button class="block w-full px-3 py-1.5 text-left text-sm text-gray-700 hover:bg-gray-50">{{ $section->is_visible ? 'Hide' : 'Show' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('website.sections.duplicate', $section) }}">@csrf
                                        <button class="block w-full px-3 py-1.5 text-left text-sm text-gray-700 hover:bg-gray-50">Duplicate</button>
                                    </form>
                                    <form method="POST" action="{{ route('website.sections.destroy', $section) }}" onsubmit="return confirm('Delete this section? This cannot be undone.')">@csrf @method('DELETE')
                                        <button class="block w-full px-3 py-1.5 text-left text-sm text-red-600 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </div>
                            <a href="{{ route('website.sections.edit', $section) }}" class="ml-1 rounded-md bg-primary px-3 py-1.5 text-sm font-semibold text-white hover:bg-secondary">Edit</a>
                        </div>
                    </li>
                @empty
                    <li class="rounded-lg border-2 border-dashed border-gray-300 p-10 text-center text-sm text-gray-500">This page is empty. Add your first section below.</li>
                @endforelse
            </ol>

            <button type="button" @click="adding = true" class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 p-4 text-sm font-semibold text-gray-600 hover:border-primary hover:text-primary">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add a section
            </button>

            {{-- Section type chooser --}}
            <div x-show="adding" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="adding = false">
                <div class="absolute inset-0 bg-gray-900/60" @click="adding = false"></div>
                <div class="relative max-h-[85vh] w-full max-w-4xl overflow-y-auto rounded-xl bg-white p-6 shadow-2xl">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Choose a section type</h2>
                        <button type="button" @click="adding = false" class="rounded p-1 text-gray-500 hover:bg-gray-100" aria-label="Close">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">It is added to the bottom of the page — you can move it afterwards.</p>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach(config('website.sections') as $type => $def)
                            <form method="POST" action="{{ route('website.sections.store', $page) }}">
                                @csrf
                                <input type="hidden" name="type" value="{{ $type }}">
                                <button class="h-full w-full rounded-lg p-4 text-left ring-1 ring-gray-200 transition hover:bg-primary/5 hover:ring-2 hover:ring-primary">
                                    <span class="block text-sm font-semibold text-gray-900">{{ $def['label'] }}</span>
                                    <span class="mt-1 block text-xs text-gray-500">{{ $def['description'] ?? '' }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Page settings --}}
        <div class="space-y-6">
            <form method="POST" action="{{ route('website.pages.update', $page) }}" class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                @csrf @method('PUT')
                <h2 class="mb-4 text-base font-semibold text-gray-900">Page settings</h2>
                @include('website.pages.settings-fields')
                <button class="mt-5 w-full rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">Save page settings</button>
            </form>

            <div class="space-y-3 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h2 class="text-base font-semibold text-gray-900">More actions</h2>
                @unless($page->is_home)
                    <form method="POST" action="{{ route('website.pages.home', $page) }}" onsubmit="return confirm('Make this page the homepage? It will be published.')">@csrf
                        <button class="w-full rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Set as homepage</button>
                    </form>
                @endunless
                <form method="POST" action="{{ route('website.pages.duplicate', $page) }}">@csrf
                    <button class="w-full rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Duplicate page</button>
                </form>
                @unless($page->is_home)
                    <form method="POST" action="{{ route('website.pages.destroy', $page) }}" onsubmit="return confirm('Delete this page and all its sections? This cannot be undone.')">@csrf @method('DELETE')
                        <button class="w-full rounded-md bg-white px-3 py-2 text-sm font-semibold text-red-600 ring-1 ring-inset ring-red-200 hover:bg-red-50">Delete page</button>
                    </form>
                @endunless
            </div>
        </div>
    </div>

    @include('website.partials.editor-scripts')
</x-app-layout>
