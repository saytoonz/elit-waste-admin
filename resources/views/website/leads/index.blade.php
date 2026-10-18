@php $select = 'rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-primary'; @endphp
<x-app-layout>
    @section('header')
        Website
    @endsection

    @include('website.partials.nav')

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Leads</h1>
            <p class="mt-2 text-sm text-gray-700">Every enquiry, bin request and quote request sent from the website. Follow up, assign and convert them into customers.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0">
            <a href="{{ route('website.leads.export', request()->query()) }}" class="block rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Download CSV</a>
        </div>
    </div>

    {{-- Pipeline --}}
    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
        @foreach(\App\Models\Website\WebsiteLead::STATUSES as $st)
            <a href="{{ route('website.leads.index', ['status' => $st]) }}" class="rounded-lg bg-white p-4 shadow-sm ring-1 {{ request('status') === $st ? 'ring-2 ring-primary' : 'ring-gray-900/5' }} hover:ring-primary">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $st }}</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $counts[$st] ?? 0 }}</p>
            </a>
        @endforeach
        <a href="{{ route('website.leads.index', ['due' => 1]) }}" class="rounded-lg bg-white p-4 shadow-sm ring-1 {{ request('due') ? 'ring-2 ring-accent-orange' : 'ring-gray-900/5' }} hover:ring-accent-orange">
            <p class="text-xs font-semibold uppercase tracking-wide text-accent-orange">Follow-up due</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $dueToday }}</p>
        </a>
        <div class="rounded-lg bg-primary/5 p-4 ring-1 ring-primary/10">
            <p class="text-xs font-semibold uppercase tracking-wide text-primary">This month</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $thisMonth }}</p>
        </div>
    </div>

    <form method="GET" class="mt-6 flex flex-wrap gap-3">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Name, phone, email, location…" class="{{ $select }} w-64">
        <select name="status" class="{{ $select }}">
            <option value="">All statuses</option>
            @foreach(\App\Models\Website\WebsiteLead::STATUSES as $st)<option @selected(request('status') === $st)>{{ $st }}</option>@endforeach
        </select>
        <select name="type" class="{{ $select }}">
            <option value="">All types</option>
            @foreach(config('website.form_types') as $key => $t)<option value="{{ $key }}" @selected(request('type') === $key)>{{ $t['label'] }}</option>@endforeach
        </select>
        <select name="assigned_to" class="{{ $select }}">
            <option value="">Anyone</option>
            @foreach($users as $u)<option value="{{ $u->id }}" @selected((string) request('assigned_to') === (string) $u->id)>{{ $u->name }}</option>@endforeach
        </select>
        <button class="rounded-md bg-gray-800 px-3 py-1.5 text-sm font-semibold text-white">Filter</button>
        @if(request()->hasAny(['q', 'status', 'type', 'assigned_to', 'due']))
            <a href="{{ route('website.leads.index') }}" class="px-2 py-1.5 text-sm font-semibold text-gray-600">Clear</a>
        @endif
    </form>

    <div class="mt-6 overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Lead</th>
                    <th class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 md:table-cell">Request</th>
                    <th class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">Location</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">Assigned</th>
                    <th class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">Received</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($leads as $lead)
                    <tr class="cursor-pointer hover:bg-gray-50" onclick="window.location='{{ route('website.leads.show', $lead) }}'">
                        <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <a href="{{ route('website.leads.show', $lead) }}" class="font-semibold text-gray-900">{{ $lead->name }}</a>
                            <div class="text-gray-500">{{ $lead->phone ?: $lead->email }}</div>
                        </td>
                        <td class="hidden px-3 py-4 text-sm text-gray-700 md:table-cell">
                            {{ $lead->type_label }}
                            @if($lead->frequency)<div class="text-xs text-gray-500">{{ $lead->frequency }}</div>@endif
                        </td>
                        <td class="hidden max-w-[14rem] truncate px-3 py-4 text-sm text-gray-500 lg:table-cell">{{ $lead->location ?: '—' }}</td>
                        <td class="px-3 py-4 text-sm">
                            <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $lead->status_badge }}">{{ $lead->status }}</span>
                            @if($lead->follow_up_at && !in_array($lead->status, ['Won', 'Lost']))
                                <div class="mt-1 text-xs {{ $lead->follow_up_at->lte(today()) ? 'font-semibold text-accent-orange' : 'text-gray-500' }}">Follow up {{ $lead->follow_up_at->format('M j') }}</div>
                            @endif
                        </td>
                        <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">{{ $lead->assignee?->name ?? '—' }}</td>
                        <td class="hidden whitespace-nowrap px-3 py-4 text-sm text-gray-500 sm:table-cell" title="{{ $lead->created_at }}">{{ $lead->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-12 text-center text-sm text-gray-500">No leads yet. They will appear here as soon as someone fills in a form on the website.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $leads->links() }}</div>
</x-app-layout>
