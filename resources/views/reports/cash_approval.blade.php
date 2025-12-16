<x-app-layout>
    @section('header')
        Cash Payment Approvals
    @endsection

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center bg-gray-50 border-b border-gray-200">
            <h3 class="text-base font-semibold leading-6 text-gray-900">Pending Approvals</h3>
            <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20 mr-2">
                Total: GHS {{ number_format($payments->sum('amount'), 2) }}
            </span>
            <div class="flex gap-2">
                 <a href="{{ route('export.csv', 'cash_approvals') }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">CSV</a>
                 <a href="{{ route('export.pdf', 'cash_approvals') }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">PDF</a>
            </div>
        </div>
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Reference</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Customer</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Recorded By</th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Approve</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($payments as $payment)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">{{ $payment->created_at->format('M d, Y H:i') }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">{{ $payment->reference }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $payment->customer->name ?? 'N/A' }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">GHS {{ number_format($payment->amount, 2) }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                             <!-- Ideally we would have a relationship to recorded_by user, assuming audit log or adding user to payment model later -->
                             Agent #{{ $payment->recorded_by ?? 'N/A' }}
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <form method="POST" action="{{ route('reports.cash.approve', $payment) }}">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900">Approve</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-sm text-gray-500 text-center">No pending cash payments.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $payments->links() }}
        </div>
    </div>
</x-app-layout>
