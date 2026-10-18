@php
    use App\Http\Controllers\Website\ContentSanitizer;
    $fields = $definition['fields'];
    $data = ContentSanitizer::clean($fields, $section->data ?? []);
    $blanks = ContentSanitizer::blanks($fields);
@endphp
<x-app-layout>
    @section('header')
        Website
    @endsection

    @include('website.partials.nav')

    <form method="POST" action="{{ route('website.sections.update', $section) }}"
          x-data="contentEditor(@js($data), @js($blanks))" class="mx-auto max-w-4xl">
        @csrf @method('PUT')
        <input type="hidden" name="data" :value="json">

        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <a href="{{ route('website.pages.edit', $section->page) }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700">&larr; {{ $section->page->title }}</a>
                <h1 class="mt-1 text-xl font-bold text-gray-900">{{ $definition['label'] }}</h1>
                <p class="text-sm text-gray-500">{{ $definition['description'] ?? '' }}</p>
            </div>
            <a href="{{ $section->page->url }}#section-{{ $section->id }}" target="_blank" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">See it on the page &nearr;</a>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 sm:p-8">
            @include('website.partials.fields', ['fields' => $fields])

            @if(collect($fields)->flatMap(fn ($f) => ($f['type'] ?? '') === 'repeater' ? $f['fields'] : [$f])->keys()->contains('value'))
                <p class="mt-6 rounded-md bg-blue-50 p-3 text-xs text-blue-800">
                    Tip: numbers can update automatically. Type <code>{customers}</code>, <code>{active_customers}</code>, <code>{zones}</code>, <code>{weight}</code> (kg or tonnes) or <code>{tonnes}</code>
                    (e.g. <code>{customers}+</code>) to show live figures from the dashboard.
                </p>
            @endif
        </div>

        <div class="sticky bottom-0 mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-gray-200 bg-gray-50/95 py-4 backdrop-blur">
            <label class="flex items-center gap-2 text-sm font-medium text-gray-900">
                <input type="checkbox" name="is_visible" value="1" @checked($section->is_visible) class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                Show this section on the website
            </label>
            <div class="flex gap-3">
                <a href="{{ route('website.pages.edit', $section->page) }}" class="px-3 py-2 text-sm font-semibold text-gray-900">Cancel</a>
                <button name="after" value="stay" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Save</button>
                <button name="after" value="page" class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">Save &amp; back to page</button>
            </div>
        </div>
    </form>

    @include('website.partials.editor-scripts')
</x-app-layout>
