<x-app-layout>
    @section('header') Expense Summary @endsection

    <form action="{{ route('reports.expenses') }}" method="GET" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-700">From</label>
                <input type="date" name="start_date" value="{{ $start }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">To</label>
                <input type="date" name="end_date" value="{{ $end }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
            </div>
            <div><button type="submit" class="w-full rounded-md bg-gray-700 px-3 py-2 text-sm font-semibold text-white">Update</button></div>
        </div>
    </form>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-xs uppercase text-gray-500">Period</div>
            <div class="text-base font-semibold text-gray-900 mt-1">{{ \Carbon\Carbon::parse($start)->format('M d, Y') }} → {{ \Carbon\Carbon::parse($end)->format('M d, Y') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-xs uppercase text-gray-500">Total Expenses</div>
            <div class="text-2xl font-bold text-red-700 mt-1">GHS {{ number_format($total, 2) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-xs uppercase text-gray-500">Transactions</div>
            <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($count) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">By Category</h3>
            <canvas id="catChart" height="180"></canvas>
            <table class="min-w-full mt-4 text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="py-2 text-left text-xs font-semibold text-gray-700 uppercase">Category</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @foreach($byCategory as $row)
                    <tr>
                        <td class="py-2"><span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full" style="background:{{ $row->category->color ?? '#9ca3af' }}"></span>{{ $row->category->name ?? '—' }}</span></td>
                        <td class="py-2 text-right font-medium">GHS {{ number_format($row->total, 2) }}</td>
                        <td class="py-2 text-right text-gray-500">{{ $total > 0 ? round(($row->total / $total) * 100, 1) : 0 }}%</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Trend (last 12 months)</h3>
            <canvas id="monthChart" height="180"></canvas>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Vendors</h3>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="py-2 text-left text-xs font-semibold text-gray-700 uppercase">Vendor</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($byVendor as $row)
                    <tr>
                        <td class="py-2">{{ $row->vendor->name ?? '— No vendor —' }}</td>
                        <td class="py-2 text-right font-medium">GHS {{ number_format($row->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="py-4 text-center text-gray-500">No vendor data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">By Payment Method</h3>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="py-2 text-left text-xs font-semibold text-gray-700 uppercase">Method</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @foreach($byMethod as $row)
                    <tr>
                        <td class="py-2">{{ $row->payment_method }}</td>
                        <td class="py-2 text-right font-medium">GHS {{ number_format($row->total, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('catChart'), {
            type: 'doughnut',
            data: {
                labels: {!! $byCategory->pluck('category.name')->toJson() !!},
                datasets: [{ data: {!! $byCategory->pluck('total')->toJson() !!}, backgroundColor: {!! $byCategory->pluck('category.color')->map(fn($c) => $c ?: '#6B7280')->toJson() !!} }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });

        new Chart(document.getElementById('monthChart'), {
            type: 'bar',
            data: {
                labels: {!! $byMonth->pluck('month')->map(fn($m) => \Carbon\Carbon::parse($m . '-01')->format('M Y'))->toJson() !!},
                datasets: [{ label: 'Expenses', data: {!! $byMonth->pluck('total')->toJson() !!}, backgroundColor: '#EF4444' }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    </script>
</x-app-layout>
