<x-app-layout>
    @section('header') Profit & Loss @endsection

    <form action="{{ route('reports.profit_loss') }}" method="GET" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
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
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 border-l-4 border-l-green-500">
            <div class="text-xs uppercase text-gray-500">Revenue</div>
            <div class="text-2xl font-bold text-green-700 mt-1">GHS {{ number_format($revenue, 2) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 border-l-4 border-l-red-500">
            <div class="text-xs uppercase text-gray-500">Expenses</div>
            <div class="text-2xl font-bold text-red-700 mt-1">GHS {{ number_format($totalExpenses, 2) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 border-l-4 {{ $netIncome >= 0 ? 'border-l-emerald-500' : 'border-l-orange-500' }}">
            <div class="text-xs uppercase text-gray-500">Net Income ({{ $margin }}%)</div>
            <div class="text-2xl font-bold {{ $netIncome >= 0 ? 'text-emerald-700' : 'text-orange-700' }} mt-1">GHS {{ number_format($netIncome, 2) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Comparison</h3>
            <canvas id="plChart" height="200"></canvas>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Expenses by Category</h3>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="py-2 text-left text-xs font-semibold text-gray-700 uppercase">Category</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Amount</th>
                        <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">% of Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($expensesByCategory as $row)
                    <tr>
                        <td class="py-2"><span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full" style="background:{{ $row->category->color ?? '#9ca3af' }}"></span>{{ $row->category->name ?? '—' }}</span></td>
                        <td class="py-2 text-right font-medium">GHS {{ number_format($row->total, 2) }}</td>
                        <td class="py-2 text-right text-gray-500">{{ $revenue > 0 ? round(($row->total / $revenue) * 100, 1) : 0 }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-gray-500">No expenses in this period.</td></tr>
                @endforelse
                </tbody>
                <tfoot class="border-t-2 border-gray-300 font-bold">
                    <tr>
                        <td class="py-2">Total</td>
                        <td class="py-2 text-right">GHS {{ number_format($totalExpenses, 2) }}</td>
                        <td class="py-2 text-right">{{ $revenue > 0 ? round(($totalExpenses / $revenue) * 100, 1) : 0 }}%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const months = {!! $months->toJson() !!};
        const revenueByMonth = {!! json_encode($monthlyRevenue) !!};
        const expensesByMonth = {!! json_encode($monthlyExpenses) !!};
        const labels = months.map(m => new Date(m + '-01').toLocaleDateString('en-GH', { month: 'short', year: 'numeric' }));
        const revData = months.map(m => parseFloat(revenueByMonth[m] || 0));
        const expData = months.map(m => parseFloat(expensesByMonth[m] || 0));

        new Chart(document.getElementById('plChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Revenue', data: revData, backgroundColor: '#10B981' },
                    { label: 'Expenses', data: expData, backgroundColor: '#EF4444' }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
        });
    </script>
</x-app-layout>
