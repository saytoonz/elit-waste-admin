@php
    $newLeadCount = \App\Models\Website\WebsiteLead::where('status', 'New')->count();
    $tabs = [
        ['website.pages.index', 'website.pages.*|website.sections.*', 'Pages'],
        ['website.posts.index', 'website.posts.*', 'News'],
        ['website.media.index', 'website.media.*', 'Media'],
        ['website.menu.index', 'website.menu.*', 'Menus'],
        ['website.leads.index', 'website.leads.*', 'Leads'],
        ['website.subscribers.index', 'website.subscribers.*', 'Subscribers'],
        ['website.settings.show', 'website.settings.*', 'Settings'],
    ];
@endphp
<div class="mb-8 flex flex-col gap-4 border-b border-gray-200 sm:flex-row sm:items-end sm:justify-between">
    <nav class="-mb-px flex gap-1 overflow-x-auto">
        @foreach($tabs as [$route, $pattern, $label])
            @php $active = request()->routeIs(...explode('|', $pattern)); @endphp
            <a href="{{ route($route) }}" class="whitespace-nowrap border-b-2 px-3 py-3 text-sm font-semibold {{ $active ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                {{ $label }}
                @if($label === 'Leads' && $newLeadCount)
                    <span class="ml-1 rounded-full bg-accent-orange px-2 py-0.5 text-xs font-bold text-white">{{ $newLeadCount }}</span>
                @endif
            </a>
        @endforeach
    </nav>
    <a href="{{ route('site.home') }}" target="_blank" class="mb-2 inline-flex items-center gap-1.5 self-start rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:self-auto">
        View website
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
    </a>
</div>

@if(session('success'))
    <div class="mb-6 rounded-md bg-green-50 p-4 text-sm font-medium text-green-800 ring-1 ring-green-600/20">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-6 rounded-md bg-red-50 p-4 text-sm font-medium text-red-800 ring-1 ring-red-600/20">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="mb-6 rounded-md bg-red-50 p-4 text-sm text-red-800 ring-1 ring-red-600/20">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
@endif
