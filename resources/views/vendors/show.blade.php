<x-app-layout>
    @section('header') {{ $vendor->name }} @endsection

    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $vendor->name }}</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $vendor->contact_person ?? 'No contact person' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('vendors.edit', $vendor) }}" class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Edit</a>
                    </div>
                </div>

                <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $vendor->phone ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $vendor->email ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Tax ID</dt><dd class="font-medium text-gray-900 font-mono">{{ $vendor->tax_id ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd>
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $vendor->is_active ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-gray-50 text-gray-700 ring-gray-600/20' }}">{{ $vendor->is_active ? 'Active' : 'Inactive' }}</span>
                    </dd></div>
                    @if($vendor->address)
                        <div class="sm:col-span-2"><dt class="text-gray-500">Address</dt><dd class="font-medium text-gray-900 whitespace-pre-wrap">{{ $vendor->address }}</dd></div>
                    @endif
                    @if($vendor->notes)
                        <div class="sm:col-span-2"><dt class="text-gray-500">Notes</dt><dd class="text-gray-700 whitespace-pre-wrap">{{ $vendor->notes }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Recent Expenses</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase">#</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Category</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($vendor->expenses as $e)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-500">{{ $e->expense_date->format('M d, Y') }}</td>
                                <td class="px-3 py-2 text-sm"><a href="{{ route('expenses.show', $e) }}" class="text-primary hover:underline">{{ $e->expense_number }}</a></td>
                                <td class="px-3 py-2 text-sm text-gray-700">{{ $e->category->name ?? '—' }}</td>
                                <td class="px-3 py-2 text-sm text-right font-medium text-gray-900">GHS {{ number_format($e->total_amount, 2) }}</td>
                                <td class="px-3 py-2 text-sm">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $e->status_badge_classes }}">{{ $e->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-6 text-center text-sm text-gray-500">No expenses recorded yet for this vendor.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-4">
                <div class="text-xs uppercase text-gray-500">Total Spent (Approved/Paid)</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">GHS {{ number_format($totalSpent, 2) }}</div>
            </div>
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-4 border-l-4 border-l-yellow-500">
                <div class="text-xs uppercase text-gray-500">Pending</div>
                <div class="text-2xl font-bold text-yellow-700 mt-1">GHS {{ number_format($pendingAmount, 2) }}</div>
            </div>
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-4">
                <h3 class="text-base font-semibold text-gray-900 mb-2">By Category</h3>
                <ul class="space-y-2 text-sm">
                    @forelse($byCategory as $row)
                        <li class="flex justify-between">
                            <span class="text-gray-700">{{ $row->category->name ?? '—' }}</span>
                            <span class="font-medium text-gray-900">GHS {{ number_format($row->total, 2) }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500">No data.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
