<x-app-layout>
    @section('header') Customer Subscriptions @endsection

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Customer Subscriptions</h1>
            <p class="mt-2 text-sm text-gray-700">Active subscriptions and force-payment flags.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none flex gap-2">
            <a href="{{ route('platform.services.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Services</a>
            <a href="{{ route('platform.subscriptions.create') }}" class="rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white hover:bg-secondary">New Subscription</a>
        </div>
    </div>

    @if(session('success'))<div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>@endif

    <div class="mt-4 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('platform.subscriptions.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-700">Service</label>
                <select name="service" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">All</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" @selected(request('service') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">All</option>
                    @foreach(['Active','Paused','Suspended','Cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700">Force-Pay</label>
                <select name="force" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">Any</option>
                    <option value="1" @selected(request('force') === '1')>On</option>
                    <option value="0" @selected(request('force') === '0')>Off</option>
                </select>
            </div>
            <div><button type="submit" class="w-full rounded-md bg-gray-700 px-3 py-2 text-sm font-semibold text-white">Filter</button></div>
        </form>
    </div>

    <div class="mt-6 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg bg-white">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Service</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Qty</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Cycle Amt</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Cycle</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Next Bill</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Force</th>
                    <th class="relative py-3 pl-3 pr-4"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($subscriptions as $sub)
                    @php $blocking = $sub->shouldBlockAccess(); @endphp
                    <tr class="{{ $blocking ? 'bg-red-50/40' : '' }}">
                        <td class="py-3 pl-4 pr-3 text-sm font-medium text-gray-900">{{ $sub->service?->name ?? '—' }}
                            @if($sub->isPastGrace())<div class="text-xs text-red-600 mt-0.5">Past grace</div>@endif
                        </td>
                        <td class="px-3 py-3 text-sm text-right text-gray-700">{{ $sub->quantity }}</td>
                        <td class="px-3 py-3 text-sm text-right font-medium text-gray-900">{{ $sub->currency }} {{ number_format($sub->cycle_amount, 2) }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $sub->billing_cycle }}</td>
                        <td class="px-3 py-3 text-sm text-gray-700">{{ $sub->next_billing_date->format('M d, Y') }}</td>
                        <td class="px-3 py-3 text-sm">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                @switch($sub->status)
                                    @case('Active') bg-green-50 text-green-700 ring-green-600/20 @break
                                    @case('Paused') bg-yellow-50 text-yellow-700 ring-yellow-600/20 @break
                                    @case('Suspended') bg-red-50 text-red-700 ring-red-600/20 @break
                                    @default bg-gray-50 text-gray-700 ring-gray-600/20
                                @endswitch">{{ $sub->status }}</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <form action="{{ route('platform.subscriptions.toggleForce', $sub) }}" method="POST">
                                @csrf
                                <button class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $sub->force_payment ? 'bg-red-50 text-red-700 ring-red-600/20' : 'bg-gray-50 text-gray-700 ring-gray-600/20' }}">
                                    {{ $sub->force_payment ? 'ON' : 'OFF' }}
                                </button>
                            </form>
                        </td>
                        <td class="py-3 pl-3 pr-4 text-right text-sm font-medium flex justify-end gap-2 items-center">
                            <form action="{{ route('platform.subscriptions.billNow', $sub) }}" method="POST" onsubmit="return confirm('Generate an invoice for the next cycle now?')">
                                @csrf
                                <button class="text-blue-600 hover:underline">Bill</button>
                            </form>
                            @if($sub->status === 'Suspended')
                                <form action="{{ route('platform.subscriptions.reactivate', $sub) }}" method="POST">
                                    @csrf
                                    <button class="text-emerald-700 hover:underline">Reactivate</button>
                                </form>
                            @endif
                            <a href="{{ route('platform.subscriptions.edit', $sub) }}" class="text-primary hover:underline">Edit</a>
                            <form action="{{ route('platform.subscriptions.destroy', $sub) }}" method="POST" onsubmit="return confirm('Cancel this subscription? (will not delete history)')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Cancel</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-3 py-8 text-center text-sm text-gray-500">No subscriptions. <a href="{{ route('platform.subscriptions.create') }}" class="text-primary font-bold hover:underline">Create one</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $subscriptions->links() }}</div>
</x-app-layout>
