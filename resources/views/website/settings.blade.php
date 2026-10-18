@php
    use App\Http\Controllers\Website\ContentSanitizer;
    $allFields = collect($groups)->flatMap(fn ($g) => $g['fields'])->all();
    $data = ContentSanitizer::clean($allFields, $values);
@endphp
<x-app-layout>
    @section('header')
        Website
    @endsection

    @include('website.partials.nav')

    <form method="POST" action="{{ route('website.settings.update') }}" x-data="contentEditor(@js($data), {})" class="mx-auto max-w-5xl">
        @csrf @method('PUT')
        <input type="hidden" name="data" :value="json">

        <div class="grid gap-8 lg:grid-cols-4" x-data="{ tab: @js(array_key_first($groups)) }">
            <nav class="flex gap-1 overflow-x-auto lg:flex-col">
                @foreach($groups as $key => $group)
                    <button type="button" @click="tab = '{{ $key }}'" class="whitespace-nowrap rounded-md px-3 py-2 text-left text-sm font-semibold"
                            :class="tab === '{{ $key }}' ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100'">{{ $group['label'] }}</button>
                @endforeach
            </nav>
            <div class="lg:col-span-3">
                @foreach($groups as $key => $group)
                    <div x-show="tab === '{{ $key }}'" @if(!$loop->first) x-cloak @endif class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 sm:p-8">
                        <h2 class="mb-6 text-base font-semibold text-gray-900">{{ $group['label'] }}</h2>
                        @include('website.partials.fields', ['fields' => $group['fields']])
                    </div>
                @endforeach
                <div class="mt-6 flex justify-end">
                    <button class="rounded-md bg-primary px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">Save settings</button>
                </div>
            </div>
        </div>
    </form>

    @include('website.partials.editor-scripts')
</x-app-layout>
