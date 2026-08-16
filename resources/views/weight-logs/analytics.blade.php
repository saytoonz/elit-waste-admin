<x-app-layout>
    @section('header')
        Weight Analytics
    @endsection

    @php
        $granularityLabels = [
            'weekly'      => 'Weekly',
            'monthly'     => 'Monthly',
            'quarterly'   => 'Quarterly',
            'half_yearly' => 'Half-Yearly',
            'yearly'      => 'Yearly',
        ];
    @endphp

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Trash Weight Analysis</h1>
            <p class="mt-2 text-sm text-gray-700">
                Net trash tonnage over time — {{ $granularityLabels[$granularity] }} view,
                {{ $from->format('M d, Y') }} to {{ $to->format('M d, Y') }}.
            </p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('weight-logs.index') }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Back to Records</a>
        </div>
    </div>

    <!-- Controls -->
    <div class="mt-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('weight-logs.analytics') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-4 items-end">
            <div>
                <label for="granularity" class="block text-sm font-medium text-gray-700">Period</label>
                <select name="granularity" id="granularity" onchange="this.form.submit()"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    @foreach($granularityLabels as $value => $label)
                        <option value="{{ $value }}" {{ $granularity == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="from" class="block text-sm font-medium text-gray-700">From</label>
                <input type="date" name="from" id="from" value="{{ $from->format('Y-m-d') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label for="to" class="block text-sm font-medium text-gray-700">To</label>
                <input type="date" name="to" id="to" value="{{ $to->format('Y-m-d') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <button type="submit" class="w-full rounded-md bg-gray-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">Apply</button>
            </div>
        </form>
    </div>

    <!-- KPI cards -->
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5 p-5">
            <dt class="truncate text-sm font-medium text-gray-500">Total Net Weight</dt>
            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($totalNet, 2) }} kg</dd>
            <dd class="text-xs text-gray-500 mt-1">{{ number_format($totalNet / 1000, 2) }} tonnes</dd>
        </div>
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5 p-5">
            <dt class="truncate text-sm font-medium text-gray-500">Weighings</dt>
            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($count) }}</dd>
        </div>
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5 p-5">
            <dt class="truncate text-sm font-medium text-gray-500">Avg per Weighing</dt>
            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($avgNet, 2) }} kg</dd>
        </div>
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5 p-5">
            <dt class="truncate text-sm font-medium text-gray-500">Heaviest Type</dt>
            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $heaviestType->trash_type ?? '—' }}</dd>
            <dd class="text-xs text-gray-500 mt-1">{{ $heaviestType ? number_format($heaviestType->total, 2) . ' kg' : 'No data' }}</dd>
        </div>
    </div>

    <!-- Time series -->
    <div class="mt-8 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Net Weight by {{ $granularityLabels[$granularity] }} Period (kg)</h3>
        <canvas id="trendChart" height="100"></canvas>
    </div>

    <!-- Breakdowns -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">By Trash Type</h3>
            <canvas id="typeChart" height="180"></canvas>
            <div class="mt-4 flow-root">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="py-2 text-left text-xs font-semibold text-gray-700 uppercase">Type</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Weighings</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Net (kg)</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($byType as $row)
                            <tr>
                                <td class="py-2 text-sm">
                                    <span class="inline-flex items-center gap-2 text-gray-900">
                                        <span class="inline-block h-3 w-3 rounded-full" style="background: {{ $typeColors[$row->trash_type] ?? '#6B7280' }}"></span>
                                        {{ $row->trash_type }}
                                    </span>
                                </td>
                                <td class="py-2 text-sm text-right text-gray-500">{{ number_format($row->cnt) }}</td>
                                <td class="py-2 text-sm text-right font-medium text-gray-900">{{ number_format($row->total, 2) }}</td>
                                <td class="py-2 text-sm text-right text-gray-500">{{ $totalNet > 0 ? number_format($row->total / $totalNet * 100, 1) : '0.0' }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-sm text-gray-500">No data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">By Vehicle (Top 10)</h3>
            <canvas id="vehicleChart" height="180"></canvas>
            <div class="mt-4 flow-root">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="py-2 text-left text-xs font-semibold text-gray-700 uppercase">Vehicle</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Trips</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Net (kg)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($byVehicle as $row)
                            <tr>
                                <td class="py-2 text-sm font-medium text-gray-900">{{ $row->vehicle_label }}</td>
                                <td class="py-2 text-sm text-right text-gray-500">{{ number_format($row->cnt) }}</td>
                                <td class="py-2 text-sm text-right font-medium text-gray-900">{{ number_format($row->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-4 text-center text-sm text-gray-500">No data for this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('trendChart'), {
            type: 'bar',
            data: {
                labels: {!! $chartLabels !!},
                datasets: [{
                    label: 'Net Weight (kg)',
                    data: {!! $chartValues !!},
                    backgroundColor: 'rgba(37,99,235,0.6)',
                    borderColor: '#2563EB',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, title: { display: true, text: 'kg' } } } }
        });

        const typeLabels = {!! $typeLabels !!};
        if (typeLabels.length) {
            new Chart(document.getElementById('typeChart'), {
                type: 'doughnut',
                data: {
                    labels: typeLabels,
                    datasets: [{ data: {!! $typeValues !!}, backgroundColor: {!! $typeColorsJson !!} }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } } }
            });
        }

        const vehicleLabels = {!! $vehicleLabels !!};
        if (vehicleLabels.length) {
            new Chart(document.getElementById('vehicleChart'), {
                type: 'bar',
                data: {
                    labels: vehicleLabels,
                    datasets: [{ label: 'Net Weight (kg)', data: {!! $vehicleValues !!}, backgroundColor: 'rgba(16,185,129,0.6)', borderColor: '#10B981', borderWidth: 1 }]
                },
                options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } }
            });
        }
    </script>
</x-app-layout>
