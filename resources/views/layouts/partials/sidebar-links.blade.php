{{--
    Sidebar navigation, shared by the desktop sidebar and the mobile drawer.
    Links are grouped into collapsible sections; a section opens automatically
    when it holds the current page, and remembers whether you left it open.
--}}
@php
    $user = auth()->user();
    $blocked = $platformBlocked ?? false;
    $is = fn (...$patterns) => request()->routeIs(...$patterns);
    $hasRole = fn ($roles) => $user?->hasRole($roles) ?? false;

    $icons = [
        'home'      => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        'zones'     => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
        'customers' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        'weighing'  => 'M12 3v2.25m0 0a2.25 2.25 0 00-2.25 2.25M12 5.25a2.25 2.25 0 012.25 2.25M6.75 7.5h10.5M6.75 7.5L3.75 16.5a3.75 3.75 0 007.5 0L8.25 7.5m9 0l-3 9a3.75 3.75 0 007.5 0l-3-9M12 18.75V21m-3 0h6',
        'invoices'  => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        'plans'     => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'broadcast' => 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46',
        'list'      => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
        'expenses'  => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
        'vendors'   => 'M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z',
        'reports'   => 'M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6',
        'users'     => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
        'website'   => 'M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418',
        'settings'  => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'bolt'      => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
        'card'      => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z',
        'chat'      => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
        'cube'      => 'M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
        'refresh'   => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99',
        'monitor'   => 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25',
    ];

    $isAdmin = $hasRole(['Owner', 'Admin']);
    $websiteNewLeads = $hasRole(['Owner', 'Admin', 'SuperAdmin'])
        ? rescue(fn () => \App\Models\Website\WebsiteLead::where('status', 'New')->count(), 0, false) : 0;
    $paymentsOff = $hasRole('SuperAdmin') && !\App\Support\PlatformConfig::paymentsEnabled();

    // [key, label, items[], tone]; item = [label, route, active patterns[], icon, visible?, badge?]
    $groups = array_filter([
        $blocked ? null : ['operations', 'Customers & Collection', [
            ['Zones', 'zones.index', ['zones.*'], 'zones'],
            ['Customers', 'customers.index', ['customers.*'], 'customers'],
            ['Weighing', 'weight-logs.index', ['weight-logs.*'], 'weighing', $user?->can('weight.view')],
        ]],
        $blocked ? null : ['billing', 'Billing', [
            ['Invoices', 'invoices.index', ['invoices.*'], 'invoices'],
            ['Service Plans', 'service_plans.index', ['service_plans.*'], 'plans'],
        ]],
        $blocked || !$isAdmin ? null : ['messaging', 'Messaging', [
            ['SMS Broadcasts', 'sms_broadcasts.index', ['sms_broadcasts.*'], 'broadcast'],
            ['SMS Log', 'sms_logs.index', ['sms_logs.*'], 'list'],
        ]],
        $blocked ? null : ['finance', 'Finance', [
            ['Expenses', 'expenses.index', ['expenses.*', 'expense_categories.*', 'recurring_expenses.*', 'expense_budgets.*'], 'expenses'],
            ['Vendors', 'vendors.index', ['vendors.*'], 'vendors'],
            ['Reports', 'reports.index', ['reports.*'], 'reports'],
        ]],
        $blocked ? null : ['admin', 'Administration', [
            ['Users', 'users.index', ['users.*'], 'users', $user?->can('manage users')],
            ['Website', 'website.pages.index', ['website.*'], 'website', $hasRole(['Owner', 'Admin', 'SuperAdmin']), $websiteNewLeads ?: null],
            ['Settings', 'settings.index', ['settings.*'], 'settings', $isAdmin],
        ]],
        ['my_billing', 'My Billing', [
            ['My Services', 'my.services.index', ['my.services.*'], 'bolt'],
            ['My Invoices', 'my.invoices.index', ['my.invoices.*'], 'card'],
            ['SMS Bundles', 'my.sms.index', ['my.sms.*'], 'chat'],
        ]],
        !$hasRole('SuperAdmin') ? null : ['platform', 'Platform (Provider)', [
            ['Catalog', 'platform.services.index', ['platform.services.*'], 'cube'],
            ['Subscriptions', 'platform.subscriptions.index', ['platform.subscriptions.*'], 'refresh'],
            ['Provider Invoices', 'platform.invoices.index', ['platform.invoices.*'], 'monitor'],
            ['Platform Settings', 'platform.settings.show', ['platform.settings.*'], 'settings', true, $paymentsOff ? 'Paused' : null],
        ], 'amber'],
    ]);

    // Drop hidden items (and groups left empty).
    $groups = collect($groups)->map(function ($g) {
        $g[2] = array_values(array_filter($g[2], fn ($i) => ($i[4] ?? true) && \Illuminate\Support\Facades\Route::has($i[1])));
        return $g;
    })->filter(fn ($g) => count($g[2]) > 0)->values();
@endphp

@unless($blocked)
<li>
    <a href="{{ route('dashboard') }}" class="{{ $is('dashboard') ? 'bg-secondary text-white' : 'text-indigo-200 hover:text-white hover:bg-secondary' }} group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6">
        <svg class="h-6 w-6 shrink-0 {{ $is('dashboard') ? 'text-white' : 'text-indigo-200 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['home'] }}" /></svg>
        Dashboard
    </a>
</li>
@endunless

@foreach($groups as [$key, $title, $items])
    @php
        $tone = $groups[$loop->index][3] ?? null;
        $groupActive = collect($items)->contains(fn ($i) => $is(...$i[2]));
        $groupBadge = collect($items)->contains(fn ($i) => !empty($i[5]));
    @endphp
    <li class="pt-2"
        x-data="{
            open: {{ $groupActive ? 'true' : 'false' }},
            init() {
                if (this.open) return;
                try { this.open = localStorage.getItem('nav.{{ $key }}') === '1' } catch (e) {}
            },
            toggle() {
                this.open = !this.open;
                try { localStorage.setItem('nav.{{ $key }}', this.open ? '1' : '0') } catch (e) {}
            }
        }">
        <button type="button" @click="toggle()" :aria-expanded="open"
                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-xs font-semibold uppercase tracking-wide {{ $tone === 'amber' ? 'text-amber-300 hover:text-amber-200' : 'text-indigo-300 hover:text-white' }}">
            <span class="flex-1">{{ $title }}</span>
            @if($groupBadge)
                <span x-show="!open" class="h-2 w-2 rounded-full {{ $tone === 'amber' ? 'bg-amber-300' : 'bg-accent-orange' }}" aria-hidden="true"></span>
            @endif
            <svg class="h-4 w-4 shrink-0 transition-transform duration-200" :class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
        </button>
        <ul role="list" class="mt-1 space-y-1" @unless($groupActive) x-cloak @endunless x-show="open"
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            @foreach($items as $item)
                @php [$label, $route, $patterns, $icon] = $item; $active = $is(...$patterns); $badge = $item[5] ?? null; @endphp
                <li>
                    <a href="{{ route($route) }}" @if($active) aria-current="page" @endif
                       class="{{ $active ? 'bg-secondary text-white' : 'text-indigo-200 hover:text-white hover:bg-secondary' }} group flex items-center gap-x-3 rounded-md p-2 text-sm font-semibold leading-6">
                        <svg class="h-6 w-6 shrink-0 {{ $active ? 'text-white' : 'text-indigo-200 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$icon] }}" />
                        </svg>
                        <span class="flex-1 truncate">{{ $label }}</span>
                        @if($badge)
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold {{ is_numeric($badge) ? 'bg-accent-orange text-white' : 'bg-amber-300 text-amber-900 text-[10px]' }}">{{ $badge }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </li>
@endforeach
