<x-app-layout>
    @section('header')
        Dashboard
    @endsection

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Revenue Card -->
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                         <div class="p-3 bg-green-50 rounded-lg">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Total Revenue</dt>
                            <dd>
                                <div class="text-2xl font-bold text-gray-900">GHS {{ number_format($totalRevenue, 2) }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Card -->
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Active Customers</dt>
                            <dd>
                                <div class="text-2xl font-bold text-gray-900">{{ $totalCustomers }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Invoices -->
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5">
             <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                         <div class="p-3 bg-amber-50 rounded-lg">
                            <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                         </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500">Unpaid Invoices</dt>
                            <dd>
                                <div class="text-2xl font-bold text-gray-900">{{ $activeInvoices }}</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

         <!-- Quick Action -->
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5 flex flex-col justify-center">
             <div class="p-5 grid grid-cols-2 gap-3">
                 <button onclick="document.location='{{ route('customers.create') }}'" class="flex flex-col items-center justify-center p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                     <svg class="w-5 h-5 text-gray-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                     <span class="text-xs font-medium text-gray-700">Add Customer</span>
                 </button>
                  <button onclick="document.location='{{ route('invoices.create') }}'" class="flex flex-col items-center justify-center p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                     <svg class="w-5 h-5 text-gray-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                     <span class="text-xs font-medium text-gray-700">New Invoice</span>
                 </button>
             </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Revenue Chart -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend (6 Months)</h3>
            <canvas id="revenueChart" height="150"></canvas>
        </div>

        <!-- Zone Chart -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Customers by Zone</h3>
             <canvas id="zoneChart" height="150"></canvas>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="mt-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Payments</h3>
        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg bg-white">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                         <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date</th>
                         <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Customer</th>
                         <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                         <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Method</th>
                         <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentPayments as $payment)
                    <tr>
                         <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">{{ $payment->created_at->format('M d, H:i') }}</td>
                         <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">{{ $payment->customer->name ?? 'Unknown' }}</td>
                         <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 font-medium">GHS {{ number_format($payment->amount, 2) }}</td>
                         <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $payment->channel }}</td>
                          <td class="whitespace-nowrap px-3 py-4 text-sm">
                             <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Success</span>
                         </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-500 text-sm">No recent payments.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart Config -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Chart
        const ctxRev = document.getElementById('revenueChart');
        new Chart(ctxRev, {
            type: 'line',
            data: {
                labels: {!! $chartLabels !!},
                datasets: [{
                    label: 'Revenue (GHS)',
                    data: {!! $chartValues !!},
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Zone Chart
        const ctxZone = document.getElementById('zoneChart');
        new Chart(ctxZone, {
            type: 'bar',
            data: {
                labels: {!! $zoneLabels !!},
                datasets: [{
                    label: 'Customers',
                    data: {!! $zoneValues !!},
                    backgroundColor: ['#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#F59E0B'],
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });
    </script>
</x-app-layout>
