@php
    use App\Support\Website;
    $site = Website::settings();
    $siteName = Website::setting('site_name', $globalCompanyName ?? config('app.name'));
    $logo = Website::image(Website::setting('logo')) ?? (!empty($globalCompanyLogo) ? asset($globalCompanyLogo) : null);
    $favicon = Website::image(Website::setting('favicon')) ?? $logo;
    $suffix = Website::setting('meta_title', $siteName);
    $pageTitle = trim($__env->yieldContent('title'));
    $fullTitle = $pageTitle ? "{$pageTitle} | {$suffix}" : $suffix;
    $description = trim($__env->yieldContent('description')) ?: Website::setting('meta_description');
    $ogImage = trim($__env->yieldContent('og_image')) ?: Website::image(Website::setting('og_image'));
    $header = Website::menu('header');
    $socials = collect(['facebook', 'instagram', 'x', 'linkedin', 'youtube', 'tiktok'])
        ->filter(fn ($k) => Website::setting($k))
        ->mapWithKeys(fn ($k) => [$k => Website::setting($k)]);
    $whatsapp = preg_replace('/\D/', '', (string) Website::setting('whatsapp'));
    $loginLabel = Website::setting('login_label');
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($ogImage)<meta property="og:image" content="{{ $ogImage }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    @if($favicon)<link rel="icon" href="{{ $favicon }}">@endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|plus-jakarta-sans:600,700,800&display=swap" rel="stylesheet" />

    <script>document.documentElement.classList.add('js')</script>
    <style>
        :root {
            --brand: {{ Website::rgb(Website::setting('primary_color'), '115 13 155') }};
            --brand-accent: {{ Website::rgb(Website::setting('accent_color'), '243 122 17') }};
            --brand-dark: {{ Website::rgb(Website::setting('dark_color'), '30 11 43') }};
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if($ga = Website::setting('ga_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($ga) }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config',@json($ga));</script>
    @endif
</head>
<body class="font-sans antialiased text-gray-900 bg-white"
      x-data="{ menuOpen: false, scrolled: false, lightbox: { open: false, items: [], index: 0 } }"
      @scroll.window="scrolled = window.scrollY > 24"
      @open-lightbox.window="lightbox = { open: true, items: $event.detail.items, index: $event.detail.index }"
      @keydown.escape.window="lightbox.open = false; menuOpen = false"
      @keydown.arrow-right.window="if (lightbox.open) lightbox.index = (lightbox.index + 1) % lightbox.items.length"
      @keydown.arrow-left.window="if (lightbox.open) lightbox.index = (lightbox.index - 1 + lightbox.items.length) % lightbox.items.length">

    @if(trim($__env->yieldContent('preview_notice')))
        <div class="bg-amber-400 text-amber-950 text-center text-sm font-semibold py-2 px-4">@yield('preview_notice')</div>
    @endif

    @if($announcement = Website::setting('announcement'))
        <div class="bg-brand-dark text-white text-sm">
            <div class="mx-auto max-w-7xl px-4 py-2 text-center">
                @if($annUrl = Website::setting('announcement_url'))
                    <a href="{{ Website::href($annUrl) }}" class="hover:underline">{{ $announcement }} <span aria-hidden="true">&rarr;</span></a>
                @else
                    {{ $announcement }}
                @endif
            </div>
        </div>
    @endif

    {{-- Header --}}
    <header class="sticky top-0 z-40 transition-all duration-300"
            :class="scrolled ? 'bg-white/95 backdrop-blur shadow-sm' : 'bg-white'">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8 h-[72px]">
            <a href="{{ route('site.home') }}" class="flex items-center gap-3 shrink-0">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $siteName }}" class="h-11 w-11 rounded-full object-cover">
                @endif
                <span class="font-display text-lg font-extrabold leading-tight text-gray-900">{{ $siteName }}</span>
            </a>

            <div class="hidden xl:flex items-center gap-1">
                @foreach($header as $item)
                    <a href="{{ $item->href }}" @if($item->new_tab) target="_blank" rel="noopener" @endif
                       class="rounded-full px-3.5 py-2 text-sm font-semibold transition {{ $item->isActive() ? 'text-brand bg-brand/10' : 'text-gray-700 hover:text-brand' }}">{{ $item->label }}</a>
                @endforeach
            </div>

            <div class="hidden xl:flex items-center gap-3">
                @if($loginLabel && Route::has('login'))
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-700 hover:text-brand">{{ $loginLabel }}</a>
                @endif
                @if($ctaLabel = Website::setting('header_cta_label'))
                    <a href="{{ Website::href(Website::setting('header_cta_url')) }}" class="rounded-full bg-brand px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand/25 transition hover:-translate-y-0.5 hover:bg-brand/90">{{ $ctaLabel }}</a>
                @endif
            </div>

            <button type="button" class="xl:hidden -mr-2 rounded-lg p-2 text-gray-800" @click="menuOpen = true" aria-label="Open menu">
                <x-site.icon name="menu" class="h-7 w-7" />
            </button>
        </nav>

        {{-- Mobile menu --}}
        <div x-cloak x-show="menuOpen" class="xl:hidden fixed inset-0 z-50" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-gray-950/50" @click="menuOpen = false" x-transition.opacity></div>
            <div class="absolute inset-y-0 right-0 w-full max-w-sm bg-white px-6 py-5 shadow-2xl overflow-y-auto"
                 x-show="menuOpen" x-transition:enter="transition duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0">
                <div class="flex items-center justify-between">
                    <span class="font-display text-lg font-extrabold">{{ $siteName }}</span>
                    <button type="button" class="-mr-2 rounded-lg p-2" @click="menuOpen = false" aria-label="Close menu"><x-site.icon name="x" class="h-6 w-6" /></button>
                </div>
                <div class="mt-6 space-y-1">
                    @foreach($header as $item)
                        <a href="{{ $item->href }}" class="block rounded-xl px-4 py-3 text-base font-semibold {{ $item->isActive() ? 'bg-brand/10 text-brand' : 'text-gray-800 hover:bg-gray-50' }}">{{ $item->label }}</a>
                    @endforeach
                </div>
                <div class="mt-6 space-y-3 border-t pt-6">
                    @if($ctaLabel = Website::setting('header_cta_label'))
                        <a href="{{ Website::href(Website::setting('header_cta_url')) }}" class="block rounded-full bg-brand px-5 py-3 text-center font-bold text-white">{{ $ctaLabel }}</a>
                    @endif
                    @if($loginLabel && Route::has('login'))
                        <a href="{{ route('login') }}" class="block rounded-full border border-gray-200 px-5 py-3 text-center font-semibold text-gray-800">{{ $loginLabel }}</a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-brand-dark text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-16 pb-10">
            <div class="grid gap-12 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <a href="{{ route('site.home') }}" class="flex items-center gap-3">
                        @if($logo)<img src="{{ $logo }}" alt="" class="h-12 w-12 rounded-full bg-white object-cover">@endif
                        <span class="font-display text-xl font-extrabold">{{ $siteName }}</span>
                    </a>
                    <p class="mt-5 max-w-sm text-sm leading-relaxed text-white/70">{{ Website::setting('footer_about') }}</p>
                    @if($socials->isNotEmpty())
                        <div class="mt-6 flex gap-3">
                            @foreach($socials as $network => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 transition hover:bg-brand-accent" aria-label="{{ ucfirst($network) }}">
                                    <x-site.social :name="$network" />
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:col-span-5">
                    @foreach([1, 2, 3] as $col)
                        @php $links = Website::menu("footer_{$col}"); @endphp
                        @if($links->isNotEmpty())
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-white/90">{{ Website::setting("footer_col{$col}_title") }}</h3>
                                <ul class="mt-4 space-y-3">
                                    @foreach($links as $item)
                                        <li><a href="{{ $item->href }}" @if($item->new_tab) target="_blank" rel="noopener" @endif class="text-sm text-white/65 transition hover:text-white">{{ $item->label }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="lg:col-span-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white/90">Get in touch</h3>
                    <ul class="mt-4 space-y-3 text-sm text-white/70">
                        @if($phone = Website::setting('phone'))
                            <li class="flex gap-3"><x-site.icon name="phone" class="h-5 w-5 shrink-0 text-brand-accent" /><a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="hover:text-white">{{ $phone }}</a></li>
                        @endif
                        @if($email = Website::setting('email'))
                            <li class="flex gap-3"><x-site.icon name="envelope" class="h-5 w-5 shrink-0 text-brand-accent" /><a href="mailto:{{ $email }}" class="hover:text-white break-all">{{ $email }}</a></li>
                        @endif
                        @if($address = Website::setting('address'))
                            <li class="flex gap-3"><x-site.icon name="map-pin" class="h-5 w-5 shrink-0 text-brand-accent" /><span>{!! nl2br(e($address)) !!}</span></li>
                        @endif
                        @if($hours = Website::setting('office_hours'))
                            <li class="flex gap-3"><x-site.icon name="clock" class="h-5 w-5 shrink-0 text-brand-accent" /><span>{{ $hours }}</span></li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="mt-14 flex flex-col gap-4 border-t border-white/10 pt-8 text-sm text-white/50 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ Website::setting('copyright') }}</p>
                @if($tagline = Website::setting('tagline'))<p>{{ $tagline }}</p>@endif
            </div>
        </div>
    </footer>

    @if($whatsapp)
        <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"
           class="fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-xl transition hover:scale-105">
            <x-site.social name="whatsapp" class="h-7 w-7" />
        </a>
    @endif

    {{-- Lightbox (used by gallery sections) --}}
    <div x-cloak x-show="lightbox.open" x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-950/90 p-4" @click.self="lightbox.open = false">
        <button type="button" class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20" @click="lightbox.open = false" aria-label="Close"><x-site.icon name="x" class="h-6 w-6" /></button>
        <button type="button" class="absolute left-3 sm:left-6 rounded-full bg-white/10 p-3 text-white hover:bg-white/20" x-show="lightbox.items.length > 1" @click="lightbox.index = (lightbox.index - 1 + lightbox.items.length) % lightbox.items.length" aria-label="Previous"><x-site.icon name="chevron-left" class="h-6 w-6" /></button>
        <figure class="max-w-5xl">
            <img :src="lightbox.items[lightbox.index]?.src" :alt="lightbox.items[lightbox.index]?.caption" class="max-h-[80vh] w-auto rounded-xl object-contain">
            <figcaption class="mt-3 text-center text-sm text-white/80" x-text="lightbox.items[lightbox.index]?.caption"></figcaption>
        </figure>
        <button type="button" class="absolute right-3 sm:right-6 rounded-full bg-white/10 p-3 text-white hover:bg-white/20" x-show="lightbox.items.length > 1" @click="lightbox.index = (lightbox.index + 1) % lightbox.items.length" aria-label="Next"><x-site.icon name="chevron-right" class="h-6 w-6" /></button>
    </div>

    <script>
        // Fade sections in as they scroll into view
        document.addEventListener('DOMContentLoaded', () => {
            const els = document.querySelectorAll('.reveal');
            if (!('IntersectionObserver' in window)) { els.forEach(el => el.classList.add('is-visible')); return; }
            const io = new IntersectionObserver((entries) => {
                entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); } });
            }, { rootMargin: '0px 0px -60px 0px' });
            els.forEach(el => io.observe(el));
        });
    </script>
</body>
</html>
