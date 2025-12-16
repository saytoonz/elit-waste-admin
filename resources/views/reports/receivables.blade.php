<x-app-layout>
    @section('header')
        Aged Receivables Report
    @endsection

    <div class="space-y-6">
        <!-- Summary Card -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <p class="text-sm font-medium text-gray-500">Total Outstanding Debt</p>
            <p class="mt-2 text-3xl font-bold text-red-600">GHS {{ number_format($totalReceivables, 2) }}</p>
        </div>

        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <form method="GET" action="{{ route('reports.receivables') }}" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="w-full sm:w-1/3">
                    <label for="zone_id" class="block text-sm font-medium text-gray-700">Filter by Zone</label>
                    <select name="zone_id" id="zone_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        <option value="">All Zones</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Future: Export Button -->
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700">Filter</button>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Customer</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Phone</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Zone</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Owing</th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                            <span class="sr-only">View</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($customers as $customer)
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $customer->name }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $customer->phone }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $customer->zone->name ?? 'N/A' }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-red-600">GHS {{ number_format($customer->invoices_sum_balance_due, 2) }}</td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <a href="{{ route('customers.show', $customer) }}" class="text-primary hover:text-secondary">View Profile</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-sm text-gray-500 text-center">No customers with outstanding debts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
