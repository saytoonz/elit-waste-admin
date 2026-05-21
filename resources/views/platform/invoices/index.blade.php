<x-app-layout>
    @section('header') Platform Invoices @endsection

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Invoices</h1>
            <p class="mt-2 text-sm text-gray-700">All invoices generated from customer subscriptions.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none flex gap-2">
            <a href="{{ route('platform.subscriptions.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Subscriptions</a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-xs uppercase text-gray-500">Unpaid</div>
            <div class="text-xl font-bold text-yellow-700 mt-1">{{ $totals['unpaid_count'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-xs uppercase text-gray-500">Overdue</div>
            <div class="text-xl font-bold text-red-700 mt-1">{{ $totals['overdue_count'] }}</div>
        </div>
    </div>

    @if(session('success'))<div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>@endif

    <div class="mt-4 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('platform.invoices.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-700">Search #</label>
                <input type="text" name="search" value="{{ request('search') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">All</option>
                    @foreach(['Pending','Paid','Partial','Overdue','Cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Currency</label>
                <select name="currency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">All</option>
                    @foreach(['USD','GHS','NGN','EUR','GBP'] as $c)
                        <option value="{{ $c }}" @selected(request('currency') === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div><button type="submit" class="w-full rounded-md bg-gray-700 px-3 py-2 text-sm font-semibold text-white">Filter</button></div>
        </form>
    </div>

    <div class="mt-6 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg bg-white">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">#</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Issued</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Period</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Kind</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Balance</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                    <th class="relative py-3 pl-3 pr-4"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($invoices as $inv)
                    <tr>
                        <td class="py-3 pl-4 pr-3 text-sm font-medium text-gray-900">
                            <a href="{{ route('platform.invoices.show', $inv) }}" class="text-primary hover:underline">{{ $inv->invoice_number }}</a>
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $inv->issued_at->format('M d, Y') }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $inv->period_start->format('M d') }} → {{ $inv->period_end->format('M d, Y') }}</td>
                        <td class="px-3 py-3 text-sm text-gray-700">{{ $inv->kind }}</td>
                        <td class="px-3 py-3 text-sm text-right font-medium text-gray-900">{{ $inv->currency }} {{ number_format($inv->total, 2) }}</td>
                        <td class="px-3 py-3 text-sm text-right font-medium {{ $inv->balance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $inv->currency }} {{ number_format($inv->balance, 2) }}</td>
                        <td class="px-3 py-3 text-sm">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $inv->status_badge_classes }}">{{ $inv->status }}</span>
                        </td>
                        <td class="py-3 pl-3 pr-4 text-right text-sm font-medium">
                            <a href="{{ route('platform.invoices.show', $inv) }}" class="text-primary hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-3 py-8 text-center text-sm text-gray-500">No invoices yet. They're auto-generated daily at 02:00 from active subscriptions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
</x-app-layout>
