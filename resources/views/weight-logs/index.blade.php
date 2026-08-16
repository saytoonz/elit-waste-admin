<x-app-layout>
    @section('header')
        Weighing Records
    @endsection

    @if (session('success'))
        <div class="mb-6 rounded-md bg-green-50 p-4 ring-1 ring-inset ring-green-600/20">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Refuse-Site Weighings</h1>
            <p class="mt-2 text-sm text-gray-700">Record the weight of trash brought to the disposal site per vehicle trip.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none flex gap-2">
            <a href="{{ route('weight-logs.analytics') }}" class="block rounded-md bg-white px-3 py-2 text-center text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Analytics</a>
            @can('weight.create')
                <a href="{{ route('weight-logs.create') }}" class="block rounded-md bg-primary px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-secondary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Add Weighing</a>
            @endcan
        </div>
    </div>

    <!-- Summary strip -->
    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5 p-5">
            <dt class="truncate text-sm font-medium text-gray-500">Net Weight (filtered)</dt>
            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($rangeTotalNet, 2) }} kg</dd>
            <dd class="text-xs text-gray-500 mt-1">{{ number_format($rangeTotalNet / 1000, 2) }} tonnes</dd>
        </div>
        <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-black ring-opacity-5 p-5">
            <dt class="truncate text-sm font-medium text-gray-500">Weighings (filtered)</dt>
            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($rangeCount) }}</dd>
        </div>
    </div>

    <!-- Filters -->
    <div class="mt-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('weight-logs.index') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-5 items-end">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Vehicle</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Plate / label..."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label for="trash_type" class="block text-sm font-medium text-gray-700">Trash Type</label>
                <select name="trash_type" id="trash_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <option value="">All Types</option>
                    @foreach($trashTypes as $type)
                        <option value="{{ $type }}" {{ request('trash_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="from" class="block text-sm font-medium text-gray-700">From</label>
                <input type="date" name="from" id="from" value="{{ request('from') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label for="to" class="block text-sm font-medium text-gray-700">To</label>
                <input type="date" name="to" id="to" value="{{ request('to') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <button type="submit" class="w-full rounded-md bg-gray-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">Filter</button>
            </div>
        </form>
    </div>

    <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Logged At</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Vehicle</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Gross</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Tare</th>
                                <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Net</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Recorded By</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($weightLogs as $log)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 sm:pl-6">{{ $log->logged_at->format('M d, Y H:i') }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">{{ $log->vehicle_label }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span class="inline-flex items-center rounded-md {{ $log->trash_type_badge_classes }} px-2 py-1 text-xs font-medium ring-1 ring-inset">{{ $log->trash_type }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-gray-500">{{ number_format($log->gross_weight, 2) }} kg</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right text-gray-500">{{ number_format($log->tare_weight, 2) }} kg</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right font-semibold text-gray-900">{{ number_format($log->net_weight, 2) }} kg</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $log->recordedBy->name ?? '—' }}</td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <div class="flex justify-end gap-2 items-center">
                                            @can('weight.edit')
                                                <a href="{{ route('weight-logs.edit', $log) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            @endcan
                                            @can('weight.delete')
                                                <span class="text-gray-300">|</span>
                                                <form method="POST" action="{{ route('weight-logs.destroy', $log) }}" onsubmit="return confirm('Delete this weighing record?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-8 text-center text-sm text-gray-500">
                                        No weighing records found.
                                        @can('weight.create')
                                            <a href="{{ route('weight-logs.create') }}" class="text-primary font-bold hover:underline">Add one now</a>.
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-4">
            {{ $weightLogs->links() }}
        </div>
    </div>
</x-app-layout>
