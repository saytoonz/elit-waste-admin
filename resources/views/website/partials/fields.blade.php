{{-- All fields of a section/settings group. $fields: definitions; repeaters edit lists of items. --}}
<div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2">
    @foreach($fields as $key => $field)
        @if(($field['type'] ?? 'text') === 'repeater')
            <div class="sm:col-span-2 rounded-lg border border-gray-200 bg-gray-50/60 p-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">{{ $field['label'] }}</h3>
                    <span class="text-xs text-gray-500" x-text="(data['{{ $key }}'] || []).length + ' item(s)'"></span>
                </div>
                <div class="mt-3 space-y-3">
                    <template x-for="(item, i) in data['{{ $key }}']" :key="i">
                        <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200" x-data="{ openItem: item._open ?? (data['{{ $key }}'].length <= 3) }">
                            <div class="flex items-center gap-3">
                                <button type="button" @click="openItem = !openItem" class="flex min-w-0 flex-1 items-center gap-2 text-left">
                                    <svg class="h-4 w-4 shrink-0 text-gray-400 transition" :class="openItem && 'rotate-90'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                                    <span class="text-sm font-semibold text-gray-900">{{ $field['item_label'] ?? 'Item' }} <span x-text="i + 1"></span></span>
                                    <span class="truncate text-sm text-gray-500" x-text="item.title || item.name || item.question || item.text || item.value || item.caption || item.quote || ''"></span>
                                </button>
                                <div class="flex shrink-0 items-center gap-1">
                                    <button type="button" @click="move('{{ $key }}', i, -1)" :disabled="i === 0" class="rounded p-1 text-gray-500 hover:bg-gray-100 disabled:opacity-30" title="Move up">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                    </button>
                                    <button type="button" @click="move('{{ $key }}', i, 1)" :disabled="i === data['{{ $key }}'].length - 1" class="rounded p-1 text-gray-500 hover:bg-gray-100 disabled:opacity-30" title="Move down">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                    </button>
                                    <button type="button" @click="remove('{{ $key }}', i)" class="rounded p-1 text-red-500 hover:bg-red-50" title="Remove">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div x-show="openItem" class="mt-4 grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                                @foreach($field['fields'] as $subKey => $sub)
                                    @include('website.partials.field', ['field' => $sub, 'model' => "item['{$subKey}']"])
                                @endforeach
                            </div>
                        </div>
                    </template>
                </div>
                <button type="button" @click="add('{{ $key }}')" class="mt-3 inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-primary shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add {{ strtolower($field['item_label'] ?? 'item') }}
                </button>
            </div>
        @else
            @include('website.partials.field', ['field' => $field, 'model' => "data['{$key}']"])
        @endif
    @endforeach
</div>
