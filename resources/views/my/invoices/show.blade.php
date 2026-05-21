<x-app-layout>
    @section('header') Invoice {{ $invoice->invoice_number }} @endsection

    @if(session('success'))<div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>@endif

    @include('my.partials.payments-paused-banner')

    <div class="max-w-4xl mx-auto bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <div class="text-xs uppercase text-gray-500">Invoice</div>
                <h2 class="text-3xl font-bold text-gray-900">{{ $invoice->invoice_number }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $invoice->kind }} • {{ $invoice->period_start->format('M d, Y') }} → {{ $invoice->period_end->format('M d, Y') }}</p>
            </div>
            <div class="text-right">
                <span class="inline-flex items-center rounded-md px-3 py-1 text-sm font-semibold ring-1 ring-inset {{ $invoice->status_badge_classes }}">{{ $invoice->status }}</span>
                <p class="text-sm text-gray-500 mt-2">Due {{ $invoice->due_date->format('M d, Y') }}</p>
            </div>
        </div>

        <table class="min-w-full text-sm">
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
                <tr class="text-lg text-gray-900"><td colspan="3" class="px-3 py-2 text-right">Total</td><td class="px-3 py-2 text-right">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td></tr>
                @if($invoice->amount_paid > 0)
                    <tr><td colspan="3" class="px-3 py-2 text-right text-green-700">Paid</td><td class="px-3 py-2 text-right text-green-700">−{{ $invoice->currency }} {{ number_format($invoice->amount_paid, 2) }}</td></tr>
                    <tr><td colspan="3" class="px-3 py-2 text-right">Balance</td><td class="px-3 py-2 text-right">{{ $invoice->currency }} {{ number_format($invoice->balance, 2) }}</td></tr>
                @endif
            </tfoot>
        </table>

        @if($invoice->status === 'Cancelled')
            <div class="mt-8 text-center">
                <p class="text-gray-700 font-semibold">This invoice has been cancelled — no payment is required.</p>
            </div>
        @elseif($invoice->balance > 0)
            @php
                $needsConversion = isset($conversion) && empty($conversion['error']) && $conversion['charge_currency'] !== $invoice->currency;
                $conversionError = $conversion['error'] ?? null;
                $paymentsEnabled = \App\Support\PlatformConfig::paymentsEnabled();
            @endphp
            <div class="mt-8 flex flex-col items-center gap-3">
                @if($paymentsEnabled)
                    <a href="{{ route('my.invoices.pay', $invoice) }}" class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-6 py-3 text-base font-semibold text-white hover:bg-emerald-500 shadow-md">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Pay {{ $invoice->currency }} {{ number_format($invoice->balance, 2) }} via Paystack
                    </a>
                    @if($needsConversion)
                        <p class="text-sm text-gray-700">You'll be charged <span class="font-semibold">{{ $conversion['charge_currency'] }} {{ number_format($conversion['charge_amount'], 2) }}</span> at rate {{ rtrim(rtrim(number_format($conversion['rate'], 4), '0'), '.') }}</p>
                    @endif
                    <p class="text-xs text-gray-500">Secure payment via Paystack — card or mobile money</p>
                @else
                    <button type="button" disabled class="inline-flex items-center gap-2 rounded-md bg-gray-300 px-6 py-3 text-base font-semibold text-gray-600 cursor-not-allowed">Payments Paused</button>
                @endif
                @if($conversionError)
                    <p class="text-sm text-red-600">{{ $conversionError }}</p>
                @endif
            </div>
        @else
            <div class="mt-8 text-center">
                <p class="text-emerald-700 font-semibold">✓ This invoice has been paid in full</p>
                @if($invoice->paid_at)<p class="text-xs text-gray-500 mt-1">{{ $invoice->paid_at->format('M d, Y H:i') }}</p>@endif
            </div>
        @endif

        @if($invoice->payments->isNotEmpty())
            <div class="mt-8 border-t border-gray-200 pt-6">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Payment History</h3>
                <ul class="space-y-2 text-sm">
                    @foreach($invoice->payments as $p)
                        <li class="flex justify-between border-b border-gray-100 pb-2">
                            <span>{{ $p->paid_at?->format('M d, Y H:i') }} • {{ $p->channel }}</span>
                            <span class="font-medium text-green-700">+{{ $p->currency }} {{ number_format($p->amount, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-app-layout>
