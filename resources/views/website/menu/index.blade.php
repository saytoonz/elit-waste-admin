@php $input = 'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm'; @endphp
<x-app-layout>
    @section('header')
        Website
    @endsection

    @include('website.partials.nav')

    <div>
        <h1 class="text-base font-semibold leading-6 text-gray-900">Menus</h1>
        <p class="mt-2 text-sm text-gray-700">Choose which links appear in the top menu and the footer columns. Footer column titles are set in <a href="{{ route('website.settings.show') }}" class="font-semibold text-primary">Settings</a>.</p>
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        @foreach($locations as $location => $label)
            @php $list = $items[$location] ?? collect(); @endphp
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5" x-data="{ adding: false }">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">{{ $label }} @if(isset($titles[$location]))<span class="font-normal text-gray-500">— “{{ $titles[$location] }}”</span>@endif</h2>
                    <button type="button" @click="adding = !adding" class="text-sm font-semibold text-primary">+ Add link</button>
                </div>

                <ul class="mt-4 divide-y divide-gray-100">
                    @forelse($list as $item)
                        <li class="py-3" x-data="{ editing: false }">
                            <div class="flex items-center gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-900">{{ $item->label }}</p>
                                    <p class="truncate text-xs text-gray-500">
                                        {{ $item->page ? 'Page: ' . $item->page->title : $item->url }}
                                        @if($item->page && $item->page->status === 'Draft')<span class="text-amber-600">(draft — hidden)</span>@endif
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('website.menu.move', $item) }}">@csrf<input type="hidden" name="direction" value="up">
                                    <button class="rounded p-1 text-gray-500 hover:bg-gray-100 disabled:opacity-30" @disabled($loop->first) title="Move up"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg></button>
                                </form>
                                <form method="POST" action="{{ route('website.menu.move', $item) }}">@csrf<input type="hidden" name="direction" value="down">
                                    <button class="rounded p-1 text-gray-500 hover:bg-gray-100 disabled:opacity-30" @disabled($loop->last) title="Move down"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg></button>
                                </form>
                                <button type="button" @click="editing = !editing" class="text-sm font-semibold text-primary">Edit</button>
                                <form method="POST" action="{{ route('website.menu.destroy', $item) }}" onsubmit="return confirm('Remove this link?')">@csrf @method('DELETE')
                                    <button class="text-sm font-semibold text-red-600">Remove</button>
                                </form>
                            </div>
                            <form x-show="editing" x-cloak method="POST" action="{{ route('website.menu.update', $item) }}" class="mt-3">
                                @csrf @method('PUT')
                                @include('website.menu.fields', ['item' => $item, 'location' => $location])
                            </form>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-gray-500">No links yet.</li>
                    @endforelse
                </ul>

                <form x-show="adding" x-cloak method="POST" action="{{ route('website.menu.store') }}" class="mt-4 border-t pt-4">
                    @csrf
                    @include('website.menu.fields', ['item' => new \App\Models\Website\WebsiteMenuItem(), 'location' => $location])
                </form>
            </div>
        @endforeach
    </div>
</x-app-layout>
