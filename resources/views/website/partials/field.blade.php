{{--
    One editor field bound with Alpine.
    $field: definition from config/website.php, $model: JS expression (e.g. data['heading'] or item['title'])
--}}
@php
    $type = $field['type'] ?? 'text';
    $input = 'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6';
    $wide = in_array($type, ['textarea', 'markdown', 'image'], true);
@endphp
<div class="{{ $wide ? 'sm:col-span-2' : '' }}">
    <label class="mb-1 block text-sm font-medium text-gray-900">{{ $field['label'] ?? '' }}</label>

    @switch($type)
        @case('textarea')
            <textarea x-model="{{ $model }}" rows="3" class="{{ $input }}"></textarea>
            <p class="mt-1 text-xs text-gray-500">Leave a blank line between paragraphs.</p>
            @break

        @case('markdown')
            <textarea x-model="{{ $model }}" rows="8" class="{{ $input }} font-mono"></textarea>
            <p class="mt-1 text-xs text-gray-500">Formatting: <code>**bold**</code>, <code>*italic*</code>, <code>## Heading</code>, <code>- list item</code>, <code>[link text](/contact)</code>. Blank line = new paragraph.</p>
            @break

        @case('select')
            <select x-model="{{ $model }}" class="{{ $input }}">
                @foreach($field['options'] ?? [] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @break

        @case('number')
            <input type="number" x-model.number="{{ $model }}" class="{{ $input }}">
            @break

        @case('url')
            <input type="text" x-model="{{ $model }}" list="site-links" placeholder="/contact or https://…" class="{{ $input }}">
            @break

        @case('color')
            <div class="flex items-center gap-2">
                <input type="color" x-model="{{ $model }}" class="h-9 w-14 cursor-pointer rounded border border-gray-300 bg-white p-1">
                <input type="text" x-model="{{ $model }}" class="{{ $input }} max-w-[8rem] font-mono">
            </div>
            @break

        @case('icon')
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open" class="flex w-full items-center gap-3 rounded-md bg-white px-3 py-1.5 text-left text-sm shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <span class="flex h-7 w-7 items-center justify-center rounded bg-primary/10 text-primary">
                        @foreach(config('website.icons') as $icon)
                            <x-site.icon :name="$icon" class="h-5 w-5" x-show="{{ $model }} === '{{ $icon }}'" />
                        @endforeach
                    </span>
                    <span x-text="{{ $model }} || 'Choose icon'" class="text-gray-700"></span>
                </button>
                <div x-show="open" x-cloak class="absolute z-20 mt-1 grid w-72 grid-cols-6 gap-1 rounded-md bg-white p-2 shadow-lg ring-1 ring-gray-200">
                    @foreach(config('website.icons') as $icon)
                        <button type="button" title="{{ $icon }}" @click="{{ $model }} = '{{ $icon }}'; open = false"
                                class="flex h-10 items-center justify-center rounded text-gray-700 hover:bg-primary/10 hover:text-primary"
                                :class="{{ $model }} === '{{ $icon }}' && 'bg-primary text-white hover:bg-primary hover:text-white'">
                            <x-site.icon :name="$icon" class="h-5 w-5" />
                        </button>
                    @endforeach
                </div>
            </div>
            @break

        @case('image')
            <div class="flex items-start gap-4">
                <div class="flex h-24 w-36 shrink-0 items-center justify-center overflow-hidden rounded-md bg-gray-100 ring-1 ring-gray-200">
                    <img x-show="{{ $model }}" :src="url({{ $model }})" class="h-full w-full object-cover" alt="">
                    <svg x-show="!{{ $model }}" class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                </div>
                <div class="flex flex-col gap-2">
                    <button type="button" @click="pick(v => {{ $model }} = v)" class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <span x-text="{{ $model }} ? 'Change image' : 'Choose image'"></span>
                    </button>
                    <button type="button" x-show="{{ $model }}" @click="{{ $model }} = null" class="text-left text-sm font-semibold text-red-600 hover:text-red-500">Remove</button>
                </div>
            </div>
            @break

        @default
            <input type="text" x-model="{{ $model }}" class="{{ $input }}">
    @endswitch
</div>
