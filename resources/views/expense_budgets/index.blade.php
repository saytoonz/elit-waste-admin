<x-app-layout>
    @section('header') Expense Budgets @endsection

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Budgets</h1>
            <p class="mt-2 text-sm text-gray-700">Set spending limits per category. Track utilization in real time.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('expense_budgets.create') }}" class="block rounded-md bg-primary px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-secondary">Set Budget</a>
        </div>
    </div>

    @if(session('success'))<div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>@endif

    <div class="mt-4 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('expense_budgets.index') }}" method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-700">Year</label>
                <select name="year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Month</label>
                <select name="month" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">All</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected($month == $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endfor
                </select>
            </div>
            <div><button type="submit" class="w-full rounded-md bg-gray-700 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-600">Filter</button></div>
        </form>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse($budgets as $b)
            @php
                $util = $b->utilization_percent;
                $barColor = $util >= 100 ? 'bg-red-500' : ($util >= $b->alert_threshold_percent ? 'bg-yellow-500' : 'bg-green-500');
            @endphp
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-5">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full" style="background-color: {{ $b->category->color }}"></span>
                            <h3 class="text-base font-semibold text-gray-900">{{ $b->category->name }}</h3>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $b->period }} • {{ $b->period_label }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('expense_budgets.edit', $b) }}" class="text-xs text-primary hover:underline">Edit</a>
                        <form action="{{ route('expense_budgets.destroy', $b) }}" method="POST" onsubmit="return confirm('Delete budget?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-600 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <div class="text-xs text-gray-500">Budget</div>
                        <div class="font-semibold text-gray-900">GHS {{ number_format($b->amount, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Spent</div>
                        <div class="font-semibold text-gray-900">GHS {{ number_format($b->spent, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Remaining</div>
                        <div class="font-semibold {{ $b->remaining > 0 ? 'text-green-700' : 'text-red-700' }}">GHS {{ number_format($b->remaining, 2) }}</div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span>Utilization</span>
                        <span class="font-semibold">{{ $util }}%</span>
                    </div>
                    <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-2 {{ $barColor }}" style="width: {{ min(100, $util) }}%"></div>
                    </div>
                    @if($util >= 100)
                        <p class="text-xs text-red-700 mt-1 font-medium">⚠ Budget exceeded</p>
                    @elseif($b->alert_enabled && $util >= $b->alert_threshold_percent)
                        <p class="text-xs text-yellow-700 mt-1 font-medium">⚠ Approaching limit ({{ $b->alert_threshold_percent }}%)</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-8 text-center text-gray-500">
                No budgets set for this period. <a href="{{ route('expense_budgets.create') }}" class="text-primary font-bold hover:underline">Set one</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $budgets->links() }}</div>
</x-app-layout>
