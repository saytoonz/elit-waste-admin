@php use App\Support\Website; $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg); $items = $s['items'] ?? []; @endphp
<section class="{{ Website::bg($bg) }} py-16 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @include('site.partials.heading', ['dark' => $dark])
        <dl class="grid gap-4 sm:gap-6 {{ [1 => '', 2 => 'grid-cols-2', 3 => 'sm:grid-cols-3'][count($items)] ?? 'grid-cols-2 lg:grid-cols-4' }}">
            @foreach($items as $item)
                <div class="reveal rounded-3xl p-5 sm:p-8 {{ $dark ? 'bg-white/10 ring-1 ring-white/15' : 'bg-white ring-1 ring-gray-100 shadow-xl shadow-gray-200/50' }}">
                    @if(!empty($item['icon']))
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $dark ? 'bg-white/15 text-white' : 'bg-brand/10 text-brand' }}"><x-site.icon :name="$item['icon']" /></div>
                    @endif
                    <dd class="mt-5 font-display text-3xl sm:text-4xl font-extrabold tracking-tight {{ $dark ? 'text-white' : 'text-gray-900' }}">{{ Website::fill($item['value'] ?? '') }}</dd>
                    <dt class="mt-1 text-sm font-semibold {{ $dark ? 'text-white/70' : 'text-gray-500' }}">{{ $item['label'] ?? '' }}</dt>
                </div>
            @endforeach
        </dl>
    </div>
</section>
