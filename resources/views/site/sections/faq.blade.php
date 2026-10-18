@php use App\Support\Website; $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg); @endphp
<section class="{{ Website::bg($bg) }} py-20 sm:py-28">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        @include('site.partials.heading', ['dark' => $dark])
        <div class="space-y-4" x-data="{ open: 0 }">
            @foreach($s['items'] ?? [] as $i => $q)
                <div class="reveal overflow-hidden rounded-2xl bg-white ring-1 ring-gray-200 transition" :class="open === {{ $i }} && 'ring-brand/40 shadow-lg shadow-brand/5'">
                    <button type="button" class="flex w-full items-center justify-between gap-6 px-6 py-5 text-left" @click="open = open === {{ $i }} ? null : {{ $i }}" :aria-expanded="open === {{ $i }}">
                        <span class="font-display text-base font-bold text-gray-900 sm:text-lg">{{ $q['question'] ?? '' }}</span>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full transition" :class="open === {{ $i }} ? 'rotate-45 bg-brand text-white' : 'bg-brand/10 text-brand'">
                            <x-site.icon name="plus" class="h-4 w-4" />
                        </span>
                    </button>
                    <div x-show="open === {{ $i }}" x-transition.opacity.duration.200ms @if($i !== 0) x-cloak @endif>
                        <div class="px-6 pb-6 leading-relaxed text-gray-600">{{ Website::paragraphs($q['answer'] ?? '') }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
