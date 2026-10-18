@php use App\Support\Website; $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg); @endphp
<section class="{{ Website::bg($bg) }} py-20 sm:py-28">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        @include('site.partials.heading', ['dark' => $dark])
        <div class="reveal grid gap-5 md:grid-cols-2">
            @foreach($s['items'] ?? [] as $row)
                <div class="flex gap-5 rounded-3xl bg-white p-6 ring-1 ring-gray-100 shadow-lg shadow-gray-200/40">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand/10 text-brand"><x-site.icon name="calendar" class="h-7 w-7" /></div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-display text-lg font-bold text-gray-900">{{ $row['title'] ?? '' }}</h3>
                            @if(!empty($row['frequency']))
                                <span class="rounded-full bg-brand-accent/15 px-3 py-0.5 text-xs font-bold text-brand-accent">{{ $row['frequency'] }}</span>
                            @endif
                        </div>
                        @if(!empty($row['days']))<p class="mt-2 text-gray-700">{{ $row['days'] }}</p>@endif
                        @if(!empty($row['note']))<p class="mt-1 text-sm text-gray-500">Best for: {{ $row['note'] }}</p>@endif
                    </div>
                </div>
            @endforeach
        </div>
        @if(!empty($s['footnote']))
            <p class="mt-8 text-center text-sm {{ $dark ? 'text-white/60' : 'text-gray-500' }}">{{ $s['footnote'] }}</p>
        @endif
    </div>
</section>
