@php
    use App\Support\Website;
    $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg);
    $cols = ['2' => 'md:grid-cols-2', '3' => 'md:grid-cols-2 lg:grid-cols-3', '4' => 'sm:grid-cols-2 lg:grid-cols-4'][$s['columns'] ?? '3'] ?? 'md:grid-cols-2 lg:grid-cols-3';
@endphp
<section class="{{ Website::bg($bg) }} py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @include('site.partials.heading', ['dark' => $dark])
        <div class="grid gap-6 {{ $cols }}">
            @foreach($s['items'] ?? [] as $item)
                <div class="reveal group rounded-3xl p-8 transition duration-300 hover:-translate-y-1 {{ $dark ? 'bg-white/5 ring-1 ring-white/10 hover:bg-white/10' : 'bg-white ring-1 ring-gray-100 hover:shadow-2xl hover:shadow-brand/10' }}">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl transition {{ $dark ? 'bg-brand-accent text-white' : 'bg-brand/10 text-brand group-hover:bg-brand group-hover:text-white' }}">
                        <x-site.icon :name="$item['icon'] ?? 'sparkles'" class="h-7 w-7" />
                    </div>
                    <h3 class="mt-6 font-display text-xl font-bold {{ $dark ? 'text-white' : 'text-gray-900' }}">{{ $item['title'] ?? '' }}</h3>
                    <p class="mt-3 leading-relaxed {{ $dark ? 'text-white/70' : 'text-gray-600' }}">{{ $item['text'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
