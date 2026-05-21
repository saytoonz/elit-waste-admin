<x-app-layout>
    @section('header') My Services @endsection

    @if(session('success'))<div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>@endif

    @include('my.partials.payments-paused-banner')

    @if($unpaidInvoices->isNotEmpty())
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-yellow-900">You have unpaid invoices</h3>
                    <p class="text-sm text-yellow-800">{{ $unpaidInvoices->count() }} invoice(s) need attention.</p>
                </div>
                <a href="{{ route('my.invoices.index') }}" class="rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white hover:bg-yellow-700">View Invoices</a>
            </div>
        </div>
    @endif

    <!-- Totals (grouped by currency) -->
    <div class="grid grid-cols-1 sm:grid-cols-{{ min(4, max(1, $totals->count() * 2)) }} gap-4 mb-6">
        @forelse($totals as $currency => $t)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="text-xs uppercase text-gray-500">Monthly ({{ $currency }})</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">{{ $currency }} {{ number_format($t['monthly'], 2) }}</div>
                <div class="text-xs text-gray-500 mt-2">{{ $currency }} {{ number_format($t['yearly'], 2) }} / year</div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sm:col-span-4">
                <p class="text-gray-500 text-sm">You have no active subscriptions yet.</p>
            </div>
        @endforelse
    </div>

    <!-- SMS Bundle Snapshot -->
    @if($activeSmsBundle)
        @php
            $smsPct = $activeSmsBundle->usage_percent;
            $smsBar = $smsPct >= 90 ? 'bg-red-500' : ($smsPct >= 75 ? 'bg-yellow-500' : 'bg-emerald-500');
            $smsDays = $activeSmsBundle->days_remaining;
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">SMS Bundle</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Expires {{ $activeSmsBundle->period_end->format('M d, Y') }} ({{ $smsDays }} day{{ $smsDays === 1 ? '' : 's' }} left)</p>
                </div>
                <a href="{{ route('my.sms.index') }}" class="text-xs text-primary hover:underline">View history →</a>
            </div>
            <div class="grid grid-cols-3 gap-3 text-sm mb-3">
                <div><div class="text-xs text-gray-500">Quota</div><div class="font-semibold text-gray-900">{{ number_format($activeSmsBundle->quantity_total) }}</div></div>
                <div><div class="text-xs text-gray-500">Used</div><div class="font-semibold text-gray-900">{{ number_format($activeSmsBundle->quantity_used) }}</div></div>
                <div><div class="text-xs text-gray-500">Remaining</div><div class="font-semibold {{ $activeSmsBundle->remaining === 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($activeSmsBundle->remaining) }}</div></div>
            </div>
            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-2 {{ $smsBar }}" style="width: {{ min(100, $smsPct) }}%"></div>
            </div>
        </div>
    @endif

    <!-- Active subscriptions -->
    @if($subscriptions->isNotEmpty())
        <h2 class="text-base font-semibold text-gray-900 mb-3">Active Subscriptions</h2>
        <div class="space-y-4">
            @foreach($subscriptions as $sub)
                @php $svc = $sub->service; @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 {{ $sub->status === 'Suspended' ? 'border-red-300 bg-red-50/40' : '' }}">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $svc->name }}</h3>
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                    @switch($sub->status)
                                        @case('Active') bg-green-50 text-green-700 ring-green-600/20 @break
                                        @case('Paused') bg-yellow-50 text-yellow-700 ring-yellow-600/20 @break
                                        @case('Suspended') bg-red-50 text-red-700 ring-red-600/20 @break
                                        @default bg-gray-50 text-gray-700 ring-gray-600/20
                                    @endswitch">{{ $sub->status }}</span>
                                @if($sub->force_payment)<span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium bg-red-100 text-red-800">Mandatory</span>@endif
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ $svc->description }}</p>
                            <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                                <div>
                                    <div class="text-xs text-gray-500">Quantity</div>
                                    <div class="font-semibold">{{ $sub->quantity }} {{ $svc->unit_label ? Str::plural($svc->unit_label, $sub->quantity) : '' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Unit Price</div>
                                    <div class="font-semibold">{{ $sub->currency }} {{ number_format($sub->unit_price, 2) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Cycle Total</div>
                                    <div class="font-semibold">{{ $sub->currency }} {{ number_format($sub->cycle_amount, 2) }} / {{ strtolower($sub->billing_cycle) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500">Next Bill</div>
                                    <div class="font-semibold {{ $sub->isOverdue() ? 'text-red-600' : 'text-gray-900' }}">{{ $sub->next_billing_date->format('M d, Y') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 min-w-[240px]">
                            @if($svc->type === 'Email' && $svc->customer_addable && $sub->status !== 'Cancelled')
                                <a href="{{ route('my.services.email.form') }}" class="text-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500">+ Add Email Account</a>
                            @elseif($svc->is_quantity_based && $svc->customer_addable && $svc->type !== 'Email' && $sub->status !== 'Cancelled')
                                <form action="{{ route('my.services.quantity', $sub) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <label class="text-xs text-gray-500 whitespace-nowrap">New qty:</label>
                                    <input type="number" name="quantity" min="{{ $svc->min_quantity }}" value="{{ $sub->quantity }}" class="block w-20 rounded-md border-0 py-1 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary">
                                    <button class="rounded-md bg-gray-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-600">Update</button>
                                </form>
                            @endif

                            @if($sub->status !== 'Cancelled')
                                <form action="{{ route('my.subscriptions.prepay', $sub) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <label class="text-xs text-gray-500 whitespace-nowrap">Pre-pay:</label>
                                    <select name="cycles" class="block w-24 rounded-md border-0 py-1 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary">
                                        @foreach([1,3,6,12] as $c)<option value="{{ $c }}">{{ $c }} cycle{{ $c > 1 ? 's' : '' }}</option>@endforeach
                                    </select>
                                    <button class="rounded-md bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary whitespace-nowrap">Generate</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Customer-purchasable add-ons -->
    @if($emailService || $otherAddable->isNotEmpty())
        <div class="mt-10">
            <h2 class="text-base font-semibold text-gray-900 mb-3">Add Services</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @if($emailService)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 ring-2 ring-emerald-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-900">{{ $emailService->name }}</h3>
                            <span class="inline-flex items-center rounded-full bg-emerald-50 text-emerald-700 px-2 py-0.5 text-xs font-medium ring-1 ring-inset ring-emerald-600/20">Add user</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ $emailService->description }}</p>
                        <div class="mt-3 text-lg font-bold text-gray-900">{{ $emailService->formatted_price }}</div>
                        @if($emailService->features)
                            <ul class="mt-2 text-xs text-gray-600 space-y-1">
                                @foreach($emailService->features as $f)
                                    <li class="flex gap-1"><span class="text-green-600">✓</span>{{ $f }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <a href="{{ route('my.services.email.form') }}" class="mt-4 block w-full rounded-md bg-primary px-3 py-2 text-center text-sm font-semibold text-white hover:bg-secondary">+ Add Email Account</a>
                        <p class="text-xs text-gray-500 mt-2 text-center">User is created automatically after payment.</p>
                    </div>
                @endif

                @foreach($otherAddable as $svc)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <h3 class="text-base font-semibold text-gray-900">{{ $svc->name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $svc->description }}</p>
                        <div class="mt-3 text-lg font-bold text-gray-900">{{ $svc->formatted_price }}</div>
                        @if($svc->features)
                            <ul class="mt-2 text-xs text-gray-600 space-y-1">
                                @foreach($svc->features as $f)
                                    <li class="flex gap-1"><span class="text-green-600">✓</span>{{ $f }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <form action="{{ route('my.services.subscribe') }}" method="POST" class="mt-4 flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="platform_service_id" value="{{ $svc->id }}">
                            @if($svc->is_quantity_based)
                                <input type="number" name="quantity" min="{{ $svc->min_quantity }}" value="{{ $svc->default_quantity }}" class="block w-20 rounded-md border-0 py-1 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300">
                            @endif
                            <button class="flex-1 rounded-md bg-primary px-3 py-1.5 text-sm font-semibold text-white hover:bg-secondary">Subscribe</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Provider-managed services (info-only) -->
    @if($managedServices->isNotEmpty())
        <div class="mt-10">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Provider-Managed Services</h2>
            <p class="text-sm text-gray-500 mb-3">These are managed directly by your service provider. Contact them to add or change.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($managedServices as $svc)
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-900">{{ $svc->name }}</h3>
                            <span class="inline-flex items-center rounded-full bg-gray-200 text-gray-700 px-2 py-0.5 text-xs font-medium">Managed</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ $svc->description }}</p>
                        <div class="mt-3 text-lg font-bold text-gray-900">{{ $svc->formatted_price }}</div>
                        @if($svc->features)
                            <ul class="mt-2 text-xs text-gray-600 space-y-1">
                                @foreach($svc->features as $f)
                                    <li class="flex gap-1"><span class="text-green-600">✓</span>{{ $f }}</li>
                                @endforeach
                            </ul>
                        @endif
                        <p class="mt-4 text-xs text-gray-500">Contact your provider to add or modify.</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-app-layout>
