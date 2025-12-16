<x-app-layout>
    @section('header')
        Audit Logs
    @endsection

    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <form method="GET" action="{{ route('reports.audit') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="user_id" class="block text-xs font-medium text-gray-700">User</label>
                    <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                     <label for="start_date" class="block text-xs font-medium text-gray-700">Start Date</label>
                     <input type="date" name="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                </div>
                 <div>
                     <label for="end_date" class="block text-xs font-medium text-gray-700">End Date</label>
                     <input type="date" name="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                </div>
                 <div>
                     <button type="submit" class="w-full bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 text-sm">Filter</button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Timestamp</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">User</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Action</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Details</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                             <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                {{ $log->user->name ?? 'System/Guest' }}
                             </td>
                             <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">{{ $log->action }}</td>
                             <td class="px-3 py-4 text-sm text-gray-500 max-w-sm truncate" title="{{ $log->details }}">{{Str::limit($log->details, 50) }}</td>
                             <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-sm text-gray-500 text-center">No audit logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
