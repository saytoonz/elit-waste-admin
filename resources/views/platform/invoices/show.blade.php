<x-app-layout>
    @section('header') Invoice {{ $invoice->invoice_number }} @endsection

    @if(session('success'))<div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>@endif

    <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-xs uppercase text-gray-500">Invoice</div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $invoice->invoice_number }}</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ $invoice->kind }} • {{ $invoice->period_start->format('M d, Y') }} → {{ $invoice->period_end->format('M d, Y') }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-md px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ $invoice->status_badge_classes }}">{{ $invoice->status }}</span>
                </div>

                <table class="min-w-full mt-6 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Description</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700 uppercase">Qty</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700 uppercase">Unit Price</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700 uppercase">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($invoice->items as $item)
                            <tr>
                                <td class="px-3 py-2">{{ $item->description }}</td>
                                <td class="px-3 py-2 text-right">{{ $item->quantity }}</td>
                                <td class="px-3 py-2 text-right">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-3 py-2 text-right font-medium">{{ $invoice->currency }} {{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-gray-300 font-bold">
                        <tr><td colspan="3" class="px-3 py-2 text-right">Subtotal</td><td class="px-3 py-2 text-right">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td></tr>
                        @if($invoice->tax > 0)<tr><td colspan="3" class="px-3 py-2 text-right">Tax</td><td class="px-3 py-2 text-right">{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td></tr>@endif
                        <tr class="text-lg"><td colspan="3" class="px-3 py-2 text-right">Total</td><td class="px-3 py-2 text-right">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td></tr>
                        @if($invoice->amount_paid > 0)
                            <tr><td colspan="3" class="px-3 py-2 text-right text-green-700">Paid</td><td class="px-3 py-2 text-right text-green-700">−{{ $invoice->currency }} {{ number_format($invoice->amount_paid, 2) }}</td></tr>
                            <tr><td colspan="3" class="px-3 py-2 text-right">Balance</td><td class="px-3 py-2 text-right">{{ $invoice->currency }} {{ number_format($invoice->balance, 2) }}</td></tr>
                        @endif
                    </tfoot>
                </table>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Payments</h3>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="py-2 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                            <th class="py-2 text-left text-xs font-semibold text-gray-700 uppercase">Reference</th>
                            <th class="py-2 text-left text-xs font-semibold text-gray-700 uppercase">Channel</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($invoice->payments as $p)
                            <tr>
                                <td class="py-2">{{ $p->paid_at?->format('M d, Y H:i') }}</td>
                                <td class="py-2 font-mono text-xs">{{ $p->reference }}</td>
                                <td class="py-2">{{ $p->channel }}</td>
                                <td class="py-2 text-right font-medium">{{ $p->currency }} {{ number_format($p->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-gray-500">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            @if($invoice->balance > 0)
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-4">
                    <h3 class="text-base font-semibold text-gray-900 mb-3">Record Manual Payment</h3>
                    <form action="{{ route('platform.invoices.markPaid', $invoice) }}" method="POST" class="space-y-2">
                        @csrf
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ $invoice->balance }}" required class="block w-full rounded-md border-0 py-1.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary">
                        <input type="text" name="channel" value="Bank Transfer" required class="block w-full rounded-md border-0 py-1.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary">
                        <input type="text" name="reference" placeholder="External ref (optional)" class="block w-full rounded-md border-0 py-1.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary">
                        <button class="w-full rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white hover:bg-secondary">Record Payment</button>
                    </form>
                </div>
                <form action="{{ route('platform.invoices.cancel', $invoice) }}" method="POST" onsubmit="return confirm('Cancel this invoice?')">
                    @csrf
                    <button class="w-full rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 ring-1 ring-inset ring-red-200 hover:bg-red-100">Cancel Invoice</button>
                </form>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-4 text-sm">
                <dl class="space-y-2">
                    <div><dt class="text-gray-500">Due Date</dt><dd class="font-medium text-gray-900">{{ $invoice->due_date->format('M d, Y') }}</dd></div>
                    <div><dt class="text-gray-500">Cycles Covered</dt><dd class="font-medium text-gray-900">{{ $invoice->cycles_covered }}</dd></div>
                    @if($invoice->paystack_reference)<div><dt class="text-gray-500">Paystack Ref</dt><dd class="font-mono text-xs">{{ $invoice->paystack_reference }}</dd></div>@endif
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
