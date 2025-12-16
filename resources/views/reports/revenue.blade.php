<x-app-layout>
    @section('header')
        Revenue Report
    @endsection

    <div class="space-y-6">
        <!-- Summary -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center">
             <div>
                <p class="text-sm font-medium text-gray-500">Total Revenue (Selected Period)</p>
                <p class="mt-2 text-3xl font-bold text-green-600">GHS {{ number_format($totalRevenue, 2) }}</p>
            </div>
            <!-- Date Filter Form -->
            <form method="GET" action="{{ route('reports.revenue') }}" class="flex gap-4 items-end">
                <div>
                     <label for="start_date" class="block text-xs font-medium text-gray-700">Start Date</label>
                     <input type="date" name="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                </div>
                <div>
                     <label for="end_date" class="block text-xs font-medium text-gray-700">End Date</label>
                     <input type="date" name="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                </div>
                <button type="submit" class="bg-primary text-white px-3 py-2 rounded-md text-sm hover:bg-secondary">Update</button>
            </form>
        </div>

        <!-- Placeholder for Chart -->
        <!-- Chart Section -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend</h3>
            <div class="relative h-64 w-full">
                <canvas id="revenueReportChart"></canvas>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('revenueReportChart');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: {!! $chartLabels !!}, // Blade outputs JSON string
                        datasets: [{
                            label: 'Daily Revenue (GHS)',
                            data: {!! $chartValues !!},
                            borderColor: '#10B981', // Green-500
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += new Intl.NumberFormat('en-GH', { style: 'currency', currency: 'GHS' }).format(context.parsed.y);
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    borderDash: [2, 4],
                                    color: '#E5E7EB'
                                },
                                ticks: {
                                    // Include GHS sign in the ticks
                                    callback: function(value, index, values) {
                                        return 'GHS ' + value;
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            });
        </script>

        <!-- Data Table -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Channel</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount Collected</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($payments as $payment)
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 sm:pl-6">{{ \Carbon\Carbon::parse($payment->date)->format('M d, Y') }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $payment->channel == 'Paystack' ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $payment->channel }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-gray-900">GHS {{ number_format($payment->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-sm text-gray-500 text-center">No revenue recorded for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
