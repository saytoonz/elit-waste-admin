<x-app-layout>
    @section('header') Vendors @endsection

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Vendors / Payees</h1>
            <p class="mt-2 text-sm text-gray-700">Suppliers, service providers, and other payees.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none flex gap-2">
            <a href="{{ route('export.csv', ['table' => 'vendors'] + request()->all()) }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">CSV</a>
            <a href="{{ route('vendors.create') }}" class="block rounded-md bg-primary px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-secondary">New Vendor</a>
        </div>
    </div>

    @if(session('success'))<div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>@endif

    <div class="mt-4 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('vendors.index') }}" method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-4 items-end">
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-700">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, phone, email, tax ID"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
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
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Contact</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Phone</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Expenses</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Total Spent</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                    <th class="relative py-3 pl-3 pr-4"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($vendors as $v)
                    <tr>
                        <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm font-medium text-gray-900">
                            <a href="{{ route('vendors.show', $v) }}" class="text-primary hover:underline">{{ $v->name }}</a>
                            @if($v->tax_id)<div class="text-xs text-gray-500 font-mono">{{ $v->tax_id }}</div>@endif
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-700">{{ $v->contact_person ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $v->phone ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm text-right text-gray-700">{{ $v->expenses_count }}</td>
                        <td class="px-3 py-3 text-sm text-right font-medium text-gray-900">GHS {{ number_format($v->total_spent ?? 0, 2) }}</td>
                        <td class="px-3 py-3 text-sm">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $v->is_active ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-gray-50 text-gray-700 ring-gray-600/20' }}">
                                {{ $v->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap py-3 pl-3 pr-4 text-right text-sm font-medium">
                            <a href="{{ route('vendors.edit', $v) }}" class="text-primary hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">No vendors yet. <a href="{{ route('vendors.create') }}" class="text-primary font-bold hover:underline">Create one</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $vendors->links() }}</div>
</x-app-layout>
