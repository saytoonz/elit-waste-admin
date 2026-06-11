<x-app-layout>
    @section('header') SMS Bundles @endsection

    @include('my.partials.payments-paused-banner')

    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Active bundle summary -->
        @if($active->isNotEmpty())
            @foreach($active as $bundle)
                @php
                    $usagePct = $bundle->usage_percent;
                    $barColor = $usagePct >= 90 ? 'bg-red-500' : ($usagePct >= 75 ? 'bg-yellow-500' : 'bg-emerald-500');
                    $daysLeft = $bundle->days_remaining;
                @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Active Bundle</h2>
                            <p class="text-sm text-gray-600 mt-1">Valid {{ $bundle->period_start->format('M d, Y') }} → <span class="font-semibold">{{ $bundle->period_end->format('M d, Y') }}</span> ({{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} left)</p>
                            <p class="text-xs text-gray-500 mt-1">1 credit = 1 SMS of up to 160 characters. Longer messages use 1 extra credit per additional 160 characters.</p>
                        </div>
                        <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                    </div>

                    <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs uppercase text-gray-500">Total Credits</div>
                            <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($bundle->quantity_total) }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">Used</div>
                            <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($bundle->quantity_used) }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">Remaining</div>
                            <div class="text-2xl font-bold {{ $bundle->remaining === 0 ? 'text-red-700' : 'text-emerald-700' }} mt-1">{{ number_format($bundle->remaining) }}</div>
                        </div>
                        <div>
                            <div class="text-xs uppercase text-gray-500">Utilization</div>
                            <div class="text-2xl font-bold text-gray-900 mt-1">{{ $usagePct }}%</div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-2 {{ $barColor }}" style="width: {{ min(100, $usagePct) }}%"></div>
                        </div>
                        @if($usagePct >= 90)
                            <p class="text-xs text-red-700 mt-2 font-medium">⚠ Less than 10% remaining — consider topping up.</p>
                        @elseif($daysLeft <= 5 && $daysLeft >= 0)
                            <p class="text-xs text-yellow-700 mt-2 font-medium">⚠ Expires in {{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} — unused credits don't roll over.</p>
                        @endif
                    </div>

                    @if($smsSubscription)
                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Auto-renews on <span class="font-semibold text-gray-900">{{ $smsSubscription->next_billing_date->format('M d, Y') }}</span> — {{ $smsSubscription->currency }} {{ number_format($smsSubscription->cycle_amount, 2) }}
                            </div>
                            <form action="{{ route('my.subscriptions.prepay', $smsSubscription) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <label class="text-xs text-gray-500">Top up:</label>
                                <select name="cycles" class="rounded-md border-0 py-1 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300">
                                    @foreach([1,3,6,12] as $c)<option value="{{ $c }}">{{ $c }} cycle{{ $c > 1 ? 's' : '' }}</option>@endforeach
                                </select>
                                <button class="rounded-md bg-primary px-3 py-1.5 text-xs font-semibold text-white hover:bg-secondary">Generate Invoice</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">No Active SMS Bundle</h2>
                @if($smsSubscription)
                    <p class="text-sm text-gray-600 mb-4">You have an SMS subscription but the latest invoice is unpaid. Pay your SMS invoice to activate the bundle.</p>
                    <a href="{{ route('my.invoices.index') }}" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">View Invoices</a>
                @elseif($smsService)
                    <p class="text-sm text-gray-600 mb-4">Subscribe to {{ $smsService->name }} ({{ $smsService->formatted_price }}) and pay the first invoice to start sending SMS.</p>
                    <form action="{{ route('my.services.subscribe') }}" method="POST" class="inline-flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="platform_service_id" value="{{ $smsService->id }}">
                        <input type="number" name="quantity" min="1" value="1" class="w-20 rounded-md border-0 py-1.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300">
                        <button class="rounded-md bg-primary px-3 py-1.5 text-sm font-semibold text-white hover:bg-secondary">Subscribe</button>
                    </form>
                @else
                    <p class="text-sm text-gray-600">SMS service is not currently available. Contact your provider.</p>
                @endif
            </div>
        @endif

        <!-- History -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Bundle History</h3>
            @if($history->isNotEmpty())
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="py-2 text-left text-xs font-semibold text-gray-700 uppercase">Period</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Quota</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Used</th>
                            <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase">Remaining</th>
                            <th class="py-2 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                            <th class="py-2 text-left text-xs font-semibold text-gray-700 uppercase">Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($history as $h)
                            <tr>
                                <td class="py-2 text-gray-700">{{ $h->period_start->format('M d') }} → {{ $h->period_end->format('M d, Y') }}</td>
                                <td class="py-2 text-right">{{ number_format($h->quantity_total) }}</td>
                                <td class="py-2 text-right">{{ number_format($h->quantity_used) }}</td>
                                <td class="py-2 text-right text-gray-500">{{ number_format($h->remaining) }}</td>
                                <td class="py-2">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset
                                        @switch($h->status)
                                            @case('Active') bg-green-50 text-green-700 ring-green-600/20 @break
                                            @case('Exhausted') bg-red-50 text-red-700 ring-red-600/20 @break
                                            @case('Expired') bg-gray-50 text-gray-700 ring-gray-600/20 @break
                                            @default bg-gray-50 text-gray-700 ring-gray-600/20
                                        @endswitch">{{ $h->status }}</span>
                                </td>
                                <td class="py-2 text-xs">
                                    @if($h->invoice)
                                        <a href="{{ route('my.invoices.show', $h->invoice->id) }}" class="text-primary hover:underline font-mono">{{ $h->invoice->invoice_number }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-sm text-gray-500">No history yet. Bundles appear here after their period ends or they're exhausted.</p>
            @endif
        </div>
    </div>
</x-app-layout>
