{{-- $label, $url, $style: primary | accent | light | outline | outline-light --}}
@if(!empty($label))
    @php
        $classes = match ($style ?? 'primary') {
            'accent'        => 'bg-brand-accent text-white shadow-lg shadow-brand-accent/30 hover:bg-brand-accent/90',
            'light'         => 'bg-white text-brand-dark hover:bg-white/90',
            'outline'       => 'border-2 border-gray-200 text-gray-900 hover:border-brand hover:text-brand',
            'outline-light' => 'border-2 border-white/40 text-white hover:bg-white/10',
            default         => 'bg-brand text-white shadow-lg shadow-brand/25 hover:bg-brand/90',
        };
        $ext = \App\Support\Website::isExternal($url ?? '');
    @endphp
    <a href="{{ \App\Support\Website::href($url ?? '') }}" @if($ext) target="_blank" rel="noopener" @endif
       class="group inline-flex items-center justify-center gap-2 rounded-full px-7 py-3.5 text-base font-bold transition hover:-translate-y-0.5 {{ $classes }}">
        {{ $label }}
        <x-site.icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1" />
    </a>
@endif
