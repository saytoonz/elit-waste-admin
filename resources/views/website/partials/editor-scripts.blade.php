{{-- Alpine components + image picker for the website editors. Include once per page. --}}
@php
    $siteLinks = \App\Models\Website\WebsitePage::orderBy('title')->get(['title', 'slug', 'is_home']);
@endphp
<datalist id="site-links">
    @foreach($siteLinks as $p)
        <option value="{{ $p->is_home ? '/' : '/' . $p->slug }}">{{ $p->title }}</option>
    @endforeach
</datalist>

{{-- Image picker modal --}}
<div x-data="mediaPicker(@js(route('website.media.picker')), @js(route('website.media.store')))" @open-media-picker.window="show($event.detail)"
     x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-gray-900/60" @click="open = false"></div>
    <div class="relative flex max-h-[85vh] w-full max-w-5xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl">
        <div class="flex flex-wrap items-center gap-3 border-b px-5 py-4">
            <h2 class="text-base font-semibold text-gray-900">Choose an image</h2>
            <input type="search" x-model="q" @input.debounce.400ms="load(true)" placeholder="Search…" class="ml-auto w-48 rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-primary">
            <label class="cursor-pointer rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white hover:bg-secondary">
                <span x-text="uploading ? 'Uploading…' : 'Upload new'"></span>
                <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple class="hidden" @change="upload($event)" :disabled="uploading">
            </label>
            <button type="button" @click="open = false" class="rounded p-1 text-gray-500 hover:bg-gray-100" aria-label="Close">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <p x-show="error" x-text="error" class="bg-red-50 px-5 py-2 text-sm text-red-700"></p>
        <div class="flex-1 overflow-y-auto p-5">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                <template x-for="item in items" :key="item.id">
                    <button type="button" @click="choose(item)" class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100 ring-1 ring-gray-200 hover:ring-4 hover:ring-primary focus:outline-none focus:ring-4 focus:ring-primary">
                        <img :src="item.url" :alt="item.alt" loading="lazy" class="h-full w-full object-cover">
                        <span class="absolute inset-x-0 bottom-0 truncate bg-black/60 px-2 py-1 text-left text-xs text-white opacity-0 group-hover:opacity-100" x-text="item.name"></span>
                    </button>
                </template>
            </div>
            <p x-show="!loading && !items.length" class="py-12 text-center text-sm text-gray-500">No images yet — upload one.</p>
            <div class="mt-5 text-center" x-show="next">
                <button type="button" @click="load()" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50" x-text="loading ? 'Loading…' : 'Load more'"></button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        const base = @js(rtrim(asset('/'), '/'));
        const toUrl = (v) => !v ? '' : (/^(https?:)?\/\//.test(v) || v.startsWith('data:')) ? v : base + '/' + String(v).replace(/^\/+/, '');

        // Form editor for sections and settings: holds the content as JSON in `data`
        Alpine.data('contentEditor', (data, blanks) => ({
            data,
            blanks,
            url: toUrl,
            add(key) {
                if (!Array.isArray(this.data[key])) this.data[key] = [];
                this.data[key].push({ ...JSON.parse(JSON.stringify(this.blanks[key] || {})), _open: true });
            },
            remove(key, i) {
                if (confirm('Remove this item?')) this.data[key].splice(i, 1);
            },
            move(key, i, dir) {
                const list = this.data[key], j = i + dir;
                if (j < 0 || j >= list.length) return;
                [list[i], list[j]] = [list[j], list[i]];
            },
            pick(setter) {
                this.$dispatch('open-media-picker', { set: setter });
            },
            get json() {
                return JSON.stringify(this.data);
            },
        }));

        // Simple single-image field for normal (non-JSON) forms
        Alpine.data('imageInput', (value) => ({
            value,
            url: toUrl,
            pick() { this.$dispatch('open-media-picker', { set: (v) => this.value = v }); },
        }));

        Alpine.data('mediaPicker', (listUrl, uploadUrl) => ({
            open: false, items: [], next: 1, loading: false, uploading: false, q: '', error: '', setter: null,
            show(detail) {
                this.setter = detail.set;
                this.error = '';
                this.open = true;
                if (!this.items.length) this.load(true);
            },
            async load(reset = false) {
                if (reset) { this.items = []; this.next = 1; }
                if (!this.next || this.loading) return;
                this.loading = true;
                try {
                    const res = await fetch(`${listUrl}?page=${this.next}&q=${encodeURIComponent(this.q)}`, { headers: { Accept: 'application/json' } });
                    const json = await res.json();
                    this.items.push(...json.items);
                    this.next = json.next;
                } catch (e) {
                    this.error = 'Could not load images.';
                } finally {
                    this.loading = false;
                }
            },
            choose(item) {
                if (this.setter) this.setter(item.path);
                this.open = false;
            },
            async upload(event) {
                const files = [...event.target.files];
                if (!files.length) return;
                this.uploading = true;
                this.error = '';
                const body = new FormData();
                files.forEach(f => body.append('files[]', f));
                try {
                    const res = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, Accept: 'application/json' },
                        body,
                    });
                    const json = await res.json();
                    if (!res.ok) { this.error = json.message || 'Upload failed.'; return; }
                    this.items.unshift(...json.items);
                    if (json.items.length === 1) this.choose(json.items[0]);
                } catch (e) {
                    this.error = 'Upload failed. Please try again.';
                } finally {
                    this.uploading = false;
                    event.target.value = '';
                }
            },
        }));
    });
</script>
