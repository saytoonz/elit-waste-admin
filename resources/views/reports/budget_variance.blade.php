<x-app-layout>
    @section('header') Budget Variance @endsection

    <form action="{{ route('reports.budget_variance') }}" method="GET" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-700">Year</label>
                <select name="year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    @foreach(range(date('Y') - 2, date('Y') + 1) as $y)
                        <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Month</label>
                <select name="month" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($month == $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endfor
                </select>
            </div>
            <div><button type="submit" class="w-full rounded-md bg-gray-700 px-3 py-2 text-sm font-semibold text-white">Update</button></div>
        </div>
    </form>

    <div class="bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Category</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Period</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Budget</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Spent</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Remaining</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase w-1/4">Utilization</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($budgets as $b)
                    @php
                        $util = $b->utilization_percent;
                        $barColor = $util >= 100 ? 'bg-red-500' : ($util >= $b->alert_threshold_percent ? 'bg-yellow-500' : 'bg-green-500');
                    @endphp
                    <tr>
                        <td class="py-3 pl-4 pr-3 text-sm">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full" style="background:{{ $b->category->color }}"></span>
                                <span class="font-medium text-gray-900">{{ $b->category->name }}</span>
                            </span>
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $b->period }} • {{ $b->period_label }}</td>
                        <td class="px-3 py-3 text-sm text-right font-medium text-gray-900">GHS {{ number_format($b->amount, 2) }}</td>
                        <td class="px-3 py-3 text-sm text-right text-gray-900">GHS {{ number_format($b->spent, 2) }}</td>
                        <td class="px-3 py-3 text-sm text-right font-medium {{ $b->remaining > 0 ? 'text-green-700' : 'text-red-700' }}">GHS {{ number_format($b->remaining, 2) }}</td>
                        <td class="px-3 py-3 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-2 {{ $barColor }}" style="width: {{ min(100, $util) }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-gray-700 w-12 text-right">{{ $util }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-sm text-gray-500">No budgets defined for this period. <a href="{{ route('expense_budgets.create') }}" class="text-primary hover:underline">Set one</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
