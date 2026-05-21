<x-app-layout>
    @section('header') Platform Services @endsection

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Platform Services Catalog</h1>
            <p class="mt-2 text-sm text-gray-700">Define what you bill customers for — hosting, email, domain, SMS, etc.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none flex gap-2">
            <a href="{{ route('platform.subscriptions.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Subscriptions</a>
            <a href="{{ route('platform.invoices.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Invoices</a>
            <a href="{{ route('platform.services.create') }}" class="rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white hover:bg-secondary">New Service</a>
        </div>
    </div>

    @if(session('success'))<div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>@endif

    <div class="mt-6 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg bg-white">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Name</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Type</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Price</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Cycle</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Qty</th>
                    <th class="px-3 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Subs</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                    <th class="relative py-3 pl-3 pr-4"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($services as $svc)
                    <tr>
                        <td class="py-3 pl-4 pr-3 text-sm font-medium text-gray-900">
                            {{ $svc->name }}
                            @if($svc->description)<p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($svc->description, 80) }}</p>@endif
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-700">{{ $svc->type }}</td>
                        <td class="px-3 py-3 text-sm text-right font-medium text-gray-900">{{ $svc->currency }} {{ number_format($svc->unit_price, 2) }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $svc->billing_cycle }}</td>
                        <td class="px-3 py-3 text-sm text-gray-700">
                            @if($svc->is_quantity_based)
                                Per {{ $svc->unit_label ?: 'unit' }}
                            @else
                                Fixed
                            @endif
                        </td>
                        <td class="px-3 py-3 text-sm text-right text-gray-700">{{ $svc->subscriptions_count }}</td>
                        <td class="px-3 py-3 text-sm">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $svc->is_active ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-gray-50 text-gray-700 ring-gray-600/20' }}">
                                {{ $svc->is_active ? 'Active' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="py-3 pl-3 pr-4 text-right text-sm font-medium flex justify-end gap-2">
                            <a href="{{ route('platform.services.edit', $svc) }}" class="text-primary hover:underline">Edit</a>
                            <form action="{{ route('platform.services.destroy', $svc) }}" method="POST" onsubmit="return confirm('Delete this service?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-3 py-8 text-center text-sm text-gray-500">No platform services. <a href="{{ route('platform.services.create') }}" class="text-primary font-bold hover:underline">Create one</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $services->links() }}</div>
</x-app-layout>
