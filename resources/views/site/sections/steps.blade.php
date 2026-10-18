@php use App\Support\Website; $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg); $items = $s['items'] ?? []; @endphp
<section class="{{ Website::bg($bg) }} py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @include('site.partials.heading', ['dark' => $dark])
        <ol class="relative grid gap-10 {{ [1 => '', 2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3'][count($items)] ?? 'md:grid-cols-2 lg:grid-cols-4' }}">
            @if(count($items) > 1)
                <div class="absolute left-0 right-0 top-10 hidden h-0.5 bg-gradient-to-r from-transparent {{ $dark ? 'via-white/20' : 'via-brand/20' }} to-transparent lg:block"></div>
            @endif
            @foreach($items as $i => $item)
                <li class="reveal relative text-center">
                    <div class="relative mx-auto flex h-20 w-20 items-center justify-center rounded-full {{ $dark ? 'bg-white text-brand' : 'bg-brand text-white' }} shadow-xl shadow-brand/20 ring-8 {{ $dark ? 'ring-white/10' : 'ring-brand/10' }}">
                        <x-site.icon :name="$item['icon'] ?? 'check'" class="h-8 w-8" />
                        <span class="absolute -right-1 -top-1 flex h-8 w-8 items-center justify-center rounded-full bg-brand-accent text-sm font-extrabold text-white">{{ $i + 1 }}</span>
                    </div>
                    <h3 class="mt-6 font-display text-xl font-bold {{ $dark ? 'text-white' : 'text-gray-900' }}">{{ $item['title'] ?? '' }}</h3>
                    <p class="mx-auto mt-3 max-w-xs leading-relaxed {{ $dark ? 'text-white/70' : 'text-gray-600' }}">{{ $item['text'] ?? '' }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
