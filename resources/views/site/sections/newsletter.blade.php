@php use App\Support\Website; $bg = $s['background'] ?? 'white'; $dark = Website::isDarkBg($bg); @endphp
<section class="{{ Website::bg($bg) }} py-16 sm:py-20" id="newsletter-{{ $section->id }}">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-col gap-8 rounded-[2rem] p-8 sm:p-12 lg:flex-row lg:items-center lg:justify-between {{ $dark ? 'bg-white/10 ring-1 ring-white/15' : 'bg-brand/5 ring-1 ring-brand/10' }}">
            <div class="max-w-xl">
                <h2 class="font-display text-2xl font-extrabold sm:text-3xl {{ $dark ? 'text-white' : 'text-gray-900' }}">{{ $s['heading'] ?? '' }}</h2>
                @if(!empty($s['text']))<p class="mt-3 {{ $dark ? 'text-white/75' : 'text-gray-600' }}">{{ $s['text'] }}</p>@endif
            </div>
            @if(session('subscribe_success'))
                <p class="flex items-center gap-2 rounded-full bg-green-50 px-5 py-3 font-semibold text-green-700"><x-site.icon name="check-circle" class="h-5 w-5" />You're subscribed. Thank you!</p>
            @else
                <form method="POST" action="{{ route('site.forms.subscribe') }}#newsletter-{{ $section->id }}" class="flex w-full max-w-md flex-col gap-3 sm:flex-row">
                    @csrf
                    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                    <label for="nl-{{ $section->id }}" class="sr-only">Email address</label>
                    <input id="nl-{{ $section->id }}" type="email" name="email" required placeholder="you@example.com"
                           class="min-w-0 flex-1 rounded-full border-0 bg-white px-5 py-3.5 text-gray-900 ring-1 ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-brand">
                    <button class="rounded-full bg-brand px-7 py-3.5 font-bold text-white transition hover:bg-brand/90">{{ $s['button'] ?? 'Subscribe' }}</button>
                </form>
            @endif
        </div>
        @error('email')<p class="mt-3 text-center text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</section>
