<x-app-layout>
    @section('header')
        Invoice #{{ $invoice->invoice_number }}
    @endsection

    <div class="max-w-3xl mx-auto bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <div class="px-4 py-6 sm:px-6 flex justify-between items-center border-b border-gray-100">
            <div>
                 <h3 class="text-base font-semibold leading-7 text-gray-900">Invoice Details</h3>
                 <p class="mt-1 text-sm text-gray-500">Issued on {{ $invoice->created_at->format('M d, Y') }}</p>
            </div>
            <div>
                <span class="inline-flex items-center rounded-md px-2 py-1 text-sm font-medium ring-1 ring-inset {{ $invoice->status == 'Paid' ? 'bg-green-50 text-green-700 ring-green-600/20' : ($invoice->status == 'Overdue' ? 'bg-red-50 text-red-700 ring-red-600/20' : 'bg-yellow-50 text-yellow-800 ring-yellow-600/20') }}">
                    {{ $invoice->status }}
                </span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-6">
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Billed To</h4>
                <p class="mt-2 text-sm font-medium text-gray-900">{{ $invoice->customer->name }}</p>
                <p class="text-sm text-gray-500">{{ $invoice->customer->phone }}</p>
                <p class="text-sm text-gray-500">{{ $invoice->customer->address }}</p>
            </div>
             <div class="text-right">
                <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Payment Details</h4>
                <p class="mt-2 text-sm text-gray-500">Due Date: <span class="text-gray-900 font-medium">{{ $invoice->due_date->format('M d, Y') }}</span></p>
                <div class="mt-4">
                    <p class="text-xs text-gray-500 uppercase">Total Amount</p>
                    <p class="text-2xl font-bold text-gray-900">GHS {{ number_format($invoice->amount, 2) }}</p>
                </div>
                 <div class="mt-2">
                    <p class="text-xs text-gray-500 uppercase">Balance Due</p>
                    <p class="text-lg font-semibold {{ $invoice->balance_due > 0 ? 'text-red-600' : 'text-green-600' }}">GHS {{ number_format($invoice->balance_due, 2) }}</p>
                </div>

                @if($invoice->balance_due > 0)
                <div class="mt-6 flex flex-col gap-2 justify-end">
                    <!-- Paystack Button -->
                     @php $payFees = \App\Services\PaystackService::feeBreakdown((float) $invoice->balance_due); @endphp
                     <a href="{{ route('payments.initiate', $invoice) }}" class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                        Pay Online (Paystack)
                    </a>
                    @if($payFees['fee'] > 0)
                        <p class="text-xs text-gray-500 text-center">Charges GHS {{ number_format($payFees['gross'], 2) }} incl. {{ rtrim(rtrim(number_format($payFees['percent'], 2), '0'), '.') }}% processing fee</p>
                    @endif
                    
                    <!-- Cash Payment Modal Trigger -->
                    <button onclick="document.getElementById('cash-modal').showModal()" class="inline-flex w-full justify-center rounded-md bg-white border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                        Record Cash Payment
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Payment History -->
        <div class="border-t border-gray-100 px-6 py-6">
            <h4 class="text-sm font-medium text-gray-900 mb-4">Payment History</h4>
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Reference</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Method</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Print</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($invoice->payments as $payment)
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 sm:pl-6">{{ $payment->created_at->format('M d, Y') }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $payment->reference }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $payment->channel }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">GHS {{ number_format($payment->amount, 2) }}</td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <a href="{{ route('payments.print', $payment) }}" target="_blank" class="text-primary hover:text-secondary">Print Receipt</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-sm text-gray-500 text-center">No payments recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($invoice->notes)
        <div class="border-t border-gray-100 px-6 py-4">
            <h4 class="text-sm font-medium text-gray-900">Notes</h4>
            <p class="mt-1 text-sm text-gray-500">{{ $invoice->notes }}</p>
        </div>
        @endif

        <div class="border-t border-gray-100 px-6 py-4 bg-gray-50 rounded-b-xl flex justify-between">
            <a href="{{ route('invoices.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Back to List</a>
            <div class="flex gap-3">
                <a href="{{ route('invoices.edit', $invoice) }}" class="text-sm font-semibold leading-6 text-primary hover:text-secondary">Edit Invoice</a>
                <!-- Future: Print / Download PDF -->
            </div>
        </div>
    </div>

    <!-- Cash Payment Modal -->
    <dialog id="cash-modal" class="modal p-0 rounded-lg shadow-xl backdrop:bg-gray-900/50">
        <div class="p-6 w-full max-w-sm bg-white">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Record Cash Payment</h3>
            <form method="POST" action="{{ route('payments.cash', $invoice) }}">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Amount Received (GHS)</label>
                    <input type="number" step="0.01" name="amount" value="{{ $invoice->balance_due }}" max="{{ $invoice->balance_due }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <p class="text-xs text-gray-500 mt-1">Max: {{ $invoice->balance_due }}</p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('cash-modal').close()" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 border rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-primary rounded-md hover:bg-secondary">Record Payment</button>
                </div>
            </form>
        </div>
    </dialog>
</x-app-layout>
