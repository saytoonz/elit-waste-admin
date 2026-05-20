<x-app-layout>
    @section('header') Recurring Expenses @endsection

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Recurring Expenses</h1>
            <p class="mt-2 text-sm text-gray-700">Schedules that auto-generate expenses (rent, salaries, subscriptions, etc.). Runs daily at 01:30.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('recurring_expenses.create') }}" class="block rounded-md bg-primary px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-secondary">New Schedule</a>
        </div>
    </div>

    @if(session('success'))<div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>@endif

    <div class="mt-4 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('recurring_expenses.index') }}" method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Frequency</label>
                <select name="frequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">All</option>
                    @foreach(['Daily','Weekly','Monthly','Quarterly','Yearly'] as $f)
                        <option value="{{ $f }}" @selected(request('frequency') === $f)>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div><button type="submit" class="w-full rounded-md bg-gray-700 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-600">Filter</button></div>
        </form>
    </div>

    <div class="mt-6 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg bg-white">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Name</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Category</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Vendor</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Amount</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Frequency</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Next Run</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                    <th class="relative py-3 pl-3 pr-4"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($recurring as $r)
                    <tr>
                        <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium text-gray-900">
                            {{ $r->name }}
                            @if($r->auto_approve)<span class="ml-1 inline-flex items-center rounded-md px-1.5 py-0.5 text-xs bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">Auto-approve</span>@endif
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-700">{{ $r->category->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-700">{{ $r->vendor->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm text-right font-medium text-gray-900">GHS {{ number_format($r->amount + $r->tax_amount, 2) }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $r->frequency }}</td>
                        <td class="px-3 py-3 text-sm text-gray-700">{{ $r->next_run_date->format('M d, Y') }}</td>
                        <td class="px-3 py-3 text-sm">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $r->is_active ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-gray-50 text-gray-700 ring-gray-600/20' }}">
                                {{ $r->is_active ? 'Active' : 'Paused' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap py-3 pl-3 pr-4 text-right text-sm font-medium flex justify-end gap-2 items-center">
                            <form action="{{ route('recurring_expenses.run', $r) }}" method="POST" onsubmit="return confirm('Generate an expense from this schedule now?')">
                                @csrf
                                <button class="text-blue-600 hover:underline">Run now</button>
                            </form>
                            <a href="{{ route('recurring_expenses.edit', $r) }}" class="text-primary hover:underline">Edit</a>
                            <form action="{{ route('recurring_expenses.destroy', $r) }}" method="POST" onsubmit="return confirm('Delete this recurring schedule?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-3 py-8 text-center text-sm text-gray-500">No recurring expenses. <a href="{{ route('recurring_expenses.create') }}" class="text-primary font-bold hover:underline">Create one</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $recurring->links() }}</div>
</x-app-layout>
