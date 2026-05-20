<x-app-layout>
    @section('header')
        Expense {{ $expense->expense_number }}
    @endsection

    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>
    @endif

    <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main column -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xs uppercase text-gray-500">Expense</div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $expense->expense_number }}</h2>
                        <p class="text-sm text-gray-500 mt-1">Recorded {{ $expense->created_at->diffForHumans() }} @if($expense->recordedBy) by {{ $expense->recordedBy->name }} @endif</p>
                    </div>
                    <span class="inline-flex items-center rounded-md px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ $expense->status_badge_classes }}">{{ $expense->status }}</span>
                </div>

                <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Category</dt>
                        <dd class="font-medium text-gray-900">{{ $expense->category->full_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Vendor</dt>
                        <dd class="font-medium text-gray-900">
                            @if($expense->vendor)
                                <a href="{{ route('vendors.show', $expense->vendor) }}" class="text-primary hover:underline">{{ $expense->vendor->name }}</a>
                            @else — @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Date</dt>
                        <dd class="font-medium text-gray-900">{{ $expense->expense_date->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Zone</dt>
                        <dd class="font-medium text-gray-900">{{ $expense->zone->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Payment Method</dt>
                        <dd class="font-medium text-gray-900">{{ $expense->payment_method }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Reference</dt>
                        <dd class="font-medium text-gray-900">{{ $expense->reference ?? '—' }}</dd>
                    </div>
                </dl>

                <div class="mt-6 border-t border-gray-100 pt-4 grid grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs uppercase text-gray-500">Amount</div>
                        <div class="text-lg font-semibold text-gray-900">GHS {{ number_format($expense->amount, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase text-gray-500">Tax</div>
                        <div class="text-lg font-semibold text-gray-900">GHS {{ number_format($expense->tax_amount, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase text-gray-500">Total</div>
                        <div class="text-lg font-bold text-primary">GHS {{ number_format($expense->total_amount, 2) }}</div>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-100 pt-4">
                    <div class="text-xs uppercase text-gray-500">Description</div>
                    <p class="mt-1 text-sm text-gray-800 whitespace-pre-wrap">{{ $expense->description }}</p>
                </div>

                @if($expense->notes)
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <div class="text-xs uppercase text-gray-500">Notes</div>
                        <p class="mt-1 text-sm text-gray-700 whitespace-pre-wrap">{{ $expense->notes }}</p>
                    </div>
                @endif

                @if($expense->rejection_reason)
                    <div class="mt-4 border-t border-red-100 pt-4 bg-red-50 -mx-6 px-6 -mb-6 pb-6 rounded-b-xl">
                        <div class="text-xs uppercase text-red-700 font-semibold">Rejection Reason</div>
                        <p class="mt-1 text-sm text-red-900">{{ $expense->rejection_reason }}</p>
                    </div>
                @endif
            </div>

            @if($expense->attachment_path)
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
                    <h3 class="text-base font-semibold text-gray-900 mb-3">Receipt</h3>
                    <a href="{{ route('expenses.attachment', $expense) }}" class="inline-flex items-center gap-2 rounded-md bg-gray-50 px-3 py-2 text-sm font-medium text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-100">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 21h10a2 2 0 002-2V7l-5-5H7a2 2 0 00-2 2v16a2 2 0 002 2z"/></svg>
                        {{ $expense->attachment_name }}
                    </a>
                </div>
            @endif
        </div>

        <!-- Side column -->
        <div class="space-y-4">
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-4">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Actions</h3>

                @hasanyrole('Owner|Admin|Accountant')
                    @if($expense->status === 'Pending')
                        <form action="{{ route('expenses.approve', $expense) }}" method="POST" class="mb-2">
                            @csrf
                            <button class="w-full rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-500">Approve</button>
                        </form>
                        <form action="{{ route('expenses.reject', $expense) }}" method="POST" x-data="{ open: false }">
                            @csrf
                            <button type="button" @click="open = !open" class="w-full rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500 mb-2">Reject</button>
                            <div x-show="open" style="display:none;" class="space-y-2">
                                <textarea name="rejection_reason" required rows="2" placeholder="Reason for rejection..."
                                    class="block w-full rounded-md border-0 py-1.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary"></textarea>
                                <button class="w-full rounded-md bg-red-700 px-3 py-2 text-sm font-semibold text-white hover:bg-red-600">Confirm Rejection</button>
                            </div>
                        </form>
                    @endif

                    @if(in_array($expense->status, ['Approved', 'Pending']))
                        <form action="{{ route('expenses.pay', $expense) }}" method="POST" class="mb-2">
                            @csrf
                            <button class="w-full rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white hover:bg-secondary">Mark as Paid</button>
                        </form>
                    @endif

                    @if(!in_array($expense->status, ['Paid', 'Cancelled']))
                        <form action="{{ route('expenses.cancel', $expense) }}" method="POST" class="mb-2" onsubmit="return confirm('Cancel this expense?');">
                            @csrf
                            <button class="w-full rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-800 ring-1 ring-inset ring-gray-300 hover:bg-gray-200">Cancel Expense</button>
                        </form>
                    @endif
                @endhasanyrole

                @if(!in_array($expense->status, ['Paid', 'Cancelled']))
                    <a href="{{ route('expenses.edit', $expense) }}" class="block text-center w-full rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 mb-2">Edit</a>
                @endif

                @if($expense->status !== 'Paid')
                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Permanently delete this expense?');">
                        @csrf
                        @method('DELETE')
                        <button class="w-full rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-200 hover:bg-red-100">Delete</button>
                    </form>
                @endif
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-4 text-sm">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Timeline</h3>
                <ul class="space-y-3 text-gray-700">
                    <li><span class="font-medium">Created:</span> {{ $expense->created_at->format('M d, Y H:i') }}</li>
                    @if($expense->approved_at)
                        <li><span class="font-medium">{{ $expense->status === 'Rejected' ? 'Rejected' : 'Approved' }}:</span> {{ $expense->approved_at->format('M d, Y H:i') }} @if($expense->approvedBy) by {{ $expense->approvedBy->name }} @endif</li>
                    @endif
                    @if($expense->paid_at)
                        <li><span class="font-medium">Paid:</span> {{ $expense->paid_at->format('M d, Y H:i') }}</li>
                    @endif
                    @if($expense->recurringExpense)
                        <li><span class="font-medium">From recurring:</span> {{ $expense->recurringExpense->name }}</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
