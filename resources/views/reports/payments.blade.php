<x-app-layout>
    @section('header')
        Payment History
    @endsection

    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900">Transactions</h2>
            <div class="flex gap-2">
                <a href="{{ route('export.csv', ['table' => 'payments'] + request()->all()) }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Export CSV</a>
                <a href="{{ route('export.pdf', ['table' => 'payments'] + request()->all()) }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Export PDF</a>
            </div>
        </div>

        <!-- Unified Filters -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <form method="GET" action="{{ route('reports.payments') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                     <label for="search" class="block text-xs font-medium text-gray-700">Search (Customer/Ref)</label>
                     <input type="text" name="search" value="{{ request('search') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                </div>
                <div>
                    <label for="channel" class="block text-xs font-medium text-gray-700">Channel</label>
                    <select name="channel" id="channel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        <option value="">All Channels</option>
                        <option value="Paystack" {{ request('channel') == 'Paystack' ? 'selected' : '' }}>Paystack</option>
                        <option value="Cash" {{ request('channel') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    </select>
                </div>
                <div>
                     <label for="start_date" class="block text-xs font-medium text-gray-700">Start Date</label>
                     <input type="date" name="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                </div>
                 <div>
                     <button type="submit" class="w-full bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm">Filter</button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Ref</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Customer</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Invoice</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Channel</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($payments as $payment)
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $payment->reference }}</td>
                             <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                @if($payment->customer)
                                    <a href="{{ route('customers.show', $payment->customer) }}" class="hover:underline hover:text-primary">{{ $payment->customer->name }}</a>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                             </td>
                             <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $payment->invoice ? $payment->invoice->invoice_number : '-' }}</td>
                             <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-gray-900">GHS {{ number_format($payment->amount, 2) }}</td>
                             <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $payment->channel }}</td>
                             <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $payment->paid_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-sm text-gray-500 text-center">No payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
