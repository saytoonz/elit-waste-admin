<x-app-layout>
    @section('header')
        Dashboard
    @endsection

    <!-- Top Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Revenue -->
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
                        <dt class="truncate text-sm font-medium text-gray-500">Total Revenue</dt>
                        <dd class="text-2xl font-bold text-gray-900">GHS {{ number_format($totalRevenue, 2) }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Month Expenses -->
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-red-50 rounded-lg">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dt class="truncate text-sm font-medium text-gray-500">Expenses ({{ now()->format('M') }})</dt>
                        <dd class="text-2xl font-bold text-gray-900">GHS {{ number_format($monthExpenses, 2) }}</dd>
                        @if($pendingExpenses > 0)
                            <dd class="text-xs text-yellow-700 mt-1">{{ number_format($pendingExpenses, 2) }} pending approval</dd>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Income This Month -->
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 {{ $monthNet >= 0 ? 'bg-emerald-50' : 'bg-orange-50' }} rounded-lg">
                            <svg class="h-6 w-6 {{ $monthNet >= 0 ? 'text-emerald-600' : 'text-orange-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dt class="truncate text-sm font-medium text-gray-500">Net ({{ now()->format('M') }})</dt>
                        <dd class="text-2xl font-bold {{ $monthNet >= 0 ? 'text-emerald-700' : 'text-orange-700' }}">GHS {{ number_format($monthNet, 2) }}</dd>
                        <dd class="text-xs text-gray-500 mt-1">Revenue {{ number_format($monthRevenue, 2) }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers -->
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dt class="truncate text-sm font-medium text-gray-500">Active Customers</dt>
                        <dd class="text-2xl font-bold text-gray-900">{{ $totalCustomers }}</dd>
                        <dd class="text-xs text-gray-500 mt-1">{{ $activeInvoices }} unpaid invoices</dd>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue vs Expenses (6 Months)</h3>
            <canvas id="revenueChart" height="120"></canvas>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Expense Categories ({{ now()->format('M') }})</h3>
            <canvas id="categoryChart" height="220"></canvas>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Customers by Zone</h3>
            <canvas id="zoneChart" height="150"></canvas>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('customers.create') }}" class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6 text-gray-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span class="text-sm font-medium text-gray-700">Add Customer</span>
                </a>
                <a href="{{ route('invoices.create') }}" class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6 text-gray-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-sm font-medium text-gray-700">New Invoice</span>
                </a>
                <a href="{{ route('expenses.create') }}" class="flex flex-col items-center justify-center p-3 bg-red-50 rounded-lg hover:bg-red-100">
                    <svg class="w-6 h-6 text-red-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                    <span class="text-sm font-medium text-red-700">Record Expense</span>
                </a>
                @hasanyrole('Owner|Admin|Accountant')
                <a href="{{ route('reports.profit_loss') }}" class="flex flex-col items-center justify-center p-3 bg-emerald-50 rounded-lg hover:bg-emerald-100">
                    <svg class="w-6 h-6 text-emerald-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 14l3-3 3 3 5-5"/></svg>
                    <span class="text-sm font-medium text-emerald-700">P&L Report</span>
                </a>
                @endhasanyrole
            </div>
        </div>
    </div>

    <!-- My Platform Billing Summary -->
    @if($myMonthlyByCurrency->isNotEmpty() || $platformUnpaidByCurrency->isNotEmpty())
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">My Subscriptions & Billing</h3>
                <div class="flex gap-2">
                    <a href="{{ route('my.services.index') }}" class="text-sm text-primary hover:underline">View Services</a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('my.invoices.index') }}" class="text-sm text-primary hover:underline">View Invoices</a>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($myMonthlyByCurrency as $currency => $monthly)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="text-xs uppercase text-gray-500">Monthly Spend ({{ $currency }})</div>
                        <div class="text-xl font-bold text-gray-900 mt-1">{{ $currency }} {{ number_format($monthly, 2) }}</div>
                    </div>
                @endforeach
                @foreach($platformUnpaidByCurrency as $row)
                    <div class="border border-yellow-200 bg-yellow-50/40 rounded-lg p-4">
                        <div class="text-xs uppercase text-yellow-700">Unpaid ({{ $row->currency }})</div>
                        <div class="text-xl font-bold text-yellow-800 mt-1">{{ $row->currency }} {{ number_format($row->balance, 2) }}</div>
                        <div class="text-xs text-yellow-700 mt-1">{{ $row->count }} invoice(s)</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Activity -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Recent Payments</h3>
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg bg-white">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Customer</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($recentPayments as $p)
                            <tr>
                                <td class="py-2 pl-4 pr-3 text-sm text-gray-500">{{ $p->created_at->format('M d') }}</td>
                                <td class="px-3 py-2 text-sm font-medium text-gray-900">{{ $p->customer->name ?? '—' }}</td>
                                <td class="px-3 py-2 text-sm text-right text-green-700 font-medium">+GHS {{ number_format($p->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-3 text-center text-sm text-gray-500">No recent payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Recent Expenses</h3>
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg bg-white">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-2 pl-4 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Category</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($recentExpenses as $e)
                            <tr>
                                <td class="py-2 pl-4 pr-3 text-sm text-gray-500">{{ $e->expense_date->format('M d') }}</td>
                                <td class="px-3 py-2 text-sm text-gray-700">
                                    <a href="{{ route('expenses.show', $e) }}" class="hover:underline">{{ $e->category->name ?? '—' }}</a>
                                </td>
                                <td class="px-3 py-2 text-sm text-right text-red-700 font-medium">-GHS {{ number_format($e->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-3 text-center text-sm text-gray-500">No recent expenses.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: {!! $chartLabels !!},
                datasets: [
                    { label: 'Revenue', data: {!! $chartValues !!}, borderColor: '#10B981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4 },
                    { label: 'Expenses', data: {!! $expenseValues !!}, borderColor: '#EF4444', backgroundColor: 'rgba(239,68,68,0.1)', fill: true, tension: 0.4 }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: {!! $categoryLabels !!},
                datasets: [{ data: {!! $categoryValues !!}, backgroundColor: {!! $categoryColors !!} }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });

        new Chart(document.getElementById('zoneChart'), {
            type: 'bar',
            data: {
                labels: {!! $zoneLabels !!},
                datasets: [{ label: 'Customers', data: {!! $zoneValues !!}, backgroundColor: ['#3B82F6','#6366F1','#8B5CF6','#EC4899','#F59E0B','#10B981','#14B8A6'] }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
    </script>
</x-app-layout>
