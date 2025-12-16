<li>
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-secondary text-white' : 'text-indigo-200 hover:text-white hover:bg-secondary' }} group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-indigo-200 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
        Dashboard
    </a>
</li>

<li>
    <a href="{{ route('zones.index') }}" class="{{ request()->routeIs('zones.*') ? 'bg-secondary text-white' : 'text-indigo-200 hover:text-white hover:bg-secondary' }} group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('zones.*') ? 'text-white' : 'text-indigo-200 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
        </svg>
        Zones
    </a>
</li>

<li>
    <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'bg-secondary text-white' : 'text-indigo-200 hover:text-white hover:bg-secondary' }} group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6">
         <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('customers.*') ? 'text-white' : 'text-indigo-200 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
        Customers
    </a>
</li>

<li>
    <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'bg-secondary text-white' : 'text-indigo-200 hover:text-white hover:bg-secondary' }} group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('invoices.*') ? 'text-white' : 'text-indigo-200 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
        </svg>
        Invoices
    </a>
</li>

<li>
    <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'bg-secondary text-white' : 'text-indigo-200 hover:text-white hover:bg-secondary' }} group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-indigo-200 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
             <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
        </svg>
        Reports
    </a>
</li>

<li>
    <a href="{{ route('service_plans.index') }}" class="{{ request()->routeIs('service_plans.*') ? 'bg-secondary text-white' : 'text-indigo-200 hover:text-white hover:bg-secondary' }} group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('service_plans.*') ? 'text-white' : 'text-indigo-200 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
             <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Service Plans
    </a>
</li>

@can('manage users')
<li>
    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'bg-secondary text-white' : 'text-indigo-200 hover:text-white hover:bg-secondary' }} group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6">
        <svg class="h-6 w-6 shrink-0 {{ request()->routeIs('users.*') ? 'text-white' : 'text-indigo-200 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
             <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
        </svg>
        Users
    </a>
</li>
@endcan
