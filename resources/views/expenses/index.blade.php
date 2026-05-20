<x-app-layout>
    @section('header')
        Expenses
    @endsection

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Expense Sheet</h1>
            <p class="mt-2 text-sm text-gray-700">All operating expenses across categories, vendors, and zones.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none flex flex-wrap gap-2">
            @hasanyrole('Owner|Admin|Accountant')
                <a href="{{ route('expense_budgets.index') }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Budgets</a>
                <a href="{{ route('recurring_expenses.index') }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Recurring</a>
                <a href="{{ route('expense_categories.index') }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Categories</a>
            @endhasanyrole
            <a href="{{ route('vendors.index') }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Vendors</a>
            <a href="{{ route('export.csv', ['table' => 'expenses'] + request()->all()) }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">CSV</a>
            <a href="{{ route('export.pdf', ['table' => 'expenses'] + request()->all()) }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">PDF</a>
            <a href="{{ route('expenses.create') }}" class="block rounded-md bg-primary px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-secondary">New Expense</a>
        </div>
    </div>

    <!-- Stat strip -->
    <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-xs font-medium text-gray-500 uppercase">Filtered Count</div>
            <div class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totals['count']) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
            <div class="text-xs font-medium text-gray-500 uppercase">Total</div>
            <div class="text-xl font-bold text-gray-900 mt-1">GHS {{ number_format($totals['total'], 2) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 border-l-4 border-l-yellow-500">
            <div class="text-xs font-medium text-gray-500 uppercase">Pending</div>
            <div class="text-xl font-bold text-yellow-700 mt-1">GHS {{ number_format($totals['pending'], 2) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 border-l-4 border-l-green-500">
            <div class="text-xs font-medium text-gray-500 uppercase">Approved / Paid</div>
            <div class="text-xl font-bold text-green-700 mt-1">GHS {{ number_format($totals['approved'], 2) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mt-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('expenses.index') }}" method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-6 items-end">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-700">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="#, description, reference, vendor"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Category</label>
                <select name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <option value="">All</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Vendor</label>
                <select name="vendor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <option value="">All</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" @selected(request('vendor_id') == $v->id)>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <option value="">All</option>
                    @foreach(['Draft','Pending','Approved','Rejected','Paid','Cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') == $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Zone</label>
                <select name="zone_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <option value="">All</option>
                    @foreach($zones as $z)
                        <option value="{{ $z->id }}" @selected(request('zone_id') == $z->id)>{{ $z->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">From</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">To</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Method</label>
                <select name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <option value="">All</option>
                    @foreach(['Cash','Bank Transfer','Mobile Money','Card','Cheque','Other'] as $m)
                        <option value="{{ $m }}" @selected(request('payment_method') == $m)>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="w-full rounded-md bg-gray-700 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-600">Filter</button>
                <a href="{{ route('expenses.index') }}" class="w-full rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>
    @endif

    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">#</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Category</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Vendor</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Description</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Amount</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Method</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                <th class="relative py-3 pl-3 pr-4"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($expenses as $expense)
                                <tr>
                                    <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium text-gray-900">{{ $expense->expense_number }}</td>
                                    <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-500">{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td class="whitespace-nowrap px-3 py-3 text-sm">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium" style="background-color: {{ $expense->category->color ?? '#E5E7EB' }}20; color: {{ $expense->category->color ?? '#374151' }};">
                                            {{ $expense->category->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-700">{{ $expense->vendor->name ?? '—' }}</td>
                                    <td class="px-3 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $expense->description }}</td>
                                    <td class="whitespace-nowrap px-3 py-3 text-sm font-medium text-right text-gray-900">GHS {{ number_format($expense->total_amount, 2) }}</td>
                                    <td class="whitespace-nowrap px-3 py-3 text-sm text-gray-500">{{ $expense->payment_method }}</td>
                                    <td class="whitespace-nowrap px-3 py-3 text-sm">
                                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $expense->status_badge_classes }}">{{ $expense->status }}</span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-3 pl-3 pr-4 text-right text-sm font-medium">
                                        <a href="{{ route('expenses.show', $expense) }}" class="text-primary hover:underline">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-3 py-10 text-center text-sm text-gray-500">
                                        No expenses found. <a href="{{ route('expenses.create') }}" class="text-primary font-bold hover:underline">Record one</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-4">{{ $expenses->links() }}</div>
    </div>
</x-app-layout>
