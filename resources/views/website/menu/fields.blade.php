@php $input = 'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm'; @endphp
<input type="hidden" name="location" value="{{ $location }}">
<div class="grid gap-3 sm:grid-cols-2" x-data="{ pageId: @js((string) ($item->website_page_id ?? '')) }">
    <div>
        <label class="mb-1 block text-xs font-medium text-gray-700">Link text</label>
        <input type="text" name="label" value="{{ $item->label }}" required maxlength="60" class="{{ $input }}">
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-gray-700">Goes to</label>
        <select name="website_page_id" x-model="pageId" class="{{ $input }}">
            <option value="">Custom link…</option>
            @foreach($pages as $p)
                <option value="{{ $p->id }}">{{ $p->title }}{{ $p->status === 'Draft' ? ' (draft)' : '' }}</option>
            @endforeach
        </select>
    </div>
    <div class="sm:col-span-2" x-show="!pageId">
        <label class="mb-1 block text-xs font-medium text-gray-700">Custom link</label>
        <input type="text" name="url" value="{{ $item->url }}" placeholder="/news, https://facebook.com/…, tel:+233…" class="{{ $input }}">
    </div>
    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="new_tab" value="1" @checked($item->new_tab) class="rounded border-gray-300 text-primary focus:ring-primary"> Open in new tab
    </label>
    <div class="text-right">
        <button class="rounded-md bg-primary px-3 py-1.5 text-sm font-semibold text-white hover:bg-secondary">Save link</button>
    </div>
</div>
