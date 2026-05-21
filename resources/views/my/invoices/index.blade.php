<x-app-layout>
    @section('header') My Invoices @endsection

    @if(session('success'))<div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>@endif

    @if($unpaidByCurrency->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-{{ min(3, $unpaidByCurrency->count()) }} gap-4 mb-6">
            @foreach($unpaidByCurrency as $row)
                <div class="bg-white rounded-lg shadow-sm p-5 border border-gray-100 border-l-4 border-l-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs uppercase text-gray-500">Unpaid Balance ({{ $row->currency }})</div>
                            <div class="text-2xl font-bold text-yellow-700 mt-1">{{ $row->currency }} {{ number_format($row->outstanding, 2) }}</div>
                            @if(!empty($row->needs_conversion))
                                <div class="text-sm text-gray-700 mt-1">≈ <span class="font-semibold">{{ $row->charge_currency }} {{ number_format($row->charge_amount, 2) }}</span> via Paystack <span class="text-xs text-gray-400">(rate {{ rtrim(rtrim(number_format($row->fx_rate, 4), '0'), '.') }})</span></div>
                            @endif
                            <div class="text-xs text-gray-500 mt-1">{{ $row->invoice_count }} invoice(s)</div>
                            @if(!empty($row->conversion_error))
                                <div class="text-xs text-red-600 mt-1">{{ $row->conversion_error }}</div>
                            @endif
                        </div>
                        <form action="{{ route('my.invoices.payAll') }}" method="POST">
                            @csrf
                            <input type="hidden" name="currency" value="{{ $row->currency }}">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-md hover:bg-emerald-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Pay All
                            </button>
                        </form>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Pays all outstanding {{ $row->currency }} invoices in a single Paystack transaction.</p>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('my.invoices.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">All</option>
                    @foreach(['Pending','Paid','Partial','Overdue','Cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
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
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Due</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Period</th>
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
                            <a href="{{ route('my.invoices.show', $inv) }}" class="text-primary hover:underline">{{ $inv->invoice_number }}</a>
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $inv->issued_at->format('M d, Y') }}</td>
                        <td class="px-3 py-3 text-sm {{ $inv->balance > 0 && $inv->due_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-500' }}">{{ $inv->due_date->format('M d, Y') }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $inv->period_start->format('M d') }} → {{ $inv->period_end->format('M d, Y') }}</td>
                        <td class="px-3 py-3 text-sm text-right font-medium">{{ $inv->currency }} {{ number_format($inv->total, 2) }}</td>
                        <td class="px-3 py-3 text-sm text-right font-medium {{ $inv->balance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $inv->currency }} {{ number_format($inv->balance, 2) }}</td>
                        <td class="px-3 py-3 text-sm">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $inv->status_badge_classes }}">{{ $inv->status }}</span>
                        </td>
                        <td class="py-3 pl-3 pr-4 text-right text-sm font-medium flex justify-end gap-2">
                            <a href="{{ route('my.invoices.show', $inv) }}" class="text-primary hover:underline">View</a>
                            @if($inv->balance > 0)
                                <a href="{{ route('my.invoices.pay', $inv) }}" class="rounded-md bg-emerald-600 px-2.5 py-1 text-white text-xs font-semibold hover:bg-emerald-500">Pay Now</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-3 py-8 text-center text-sm text-gray-500">No invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
</x-app-layout>
