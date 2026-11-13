<x-app-layout>
    @section('header') SMS Log @endsection

    @php
        $badge = fn($s) => match ($s) {
            'Sent'    => 'bg-green-50 text-green-700 ring-green-600/20',
            'Queued'  => 'bg-blue-50 text-blue-700 ring-blue-600/20',
            'Failed'  => 'bg-red-50 text-red-700 ring-red-600/20',
            'Skipped' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
            default   => 'bg-gray-50 text-gray-700 ring-gray-600/20',
        };
        $total = $counts->sum('n');
        $chipUrl = fn($status) => route('sms_logs.index', array_filter(array_merge(request()->except('page', 'status'), ['status' => $status])));
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        @if(session('success'))
            <div class="rounded-md bg-green-50 p-4 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-md bg-red-50 p-4 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-gray-600">
                Every SMS the system sends: payments, invoices, reminders, broadcasts and alerts.
                <span class="text-gray-500">"Sent" means MyCSMS accepted it for delivery — MyCSMS does not provide handset delivery reports for SMS.</span>
            </p>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600">
                @if($bundle)
                    <p>
                        <span class="font-semibold {{ $bundle->remaining === 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($bundle->remaining) }}</span>
                        bundle credits · expires {{ $bundle->period_end->format('M d, Y') }}
                    </p>
                @else
                    <p class="text-amber-700 font-medium">No active SMS bundle.</p>
                @endif
                @if($gatewayBalance !== null)
                    <p title="Balance on the MyCSMS gateway account. When it reaches zero every SMS fails.">
                        Gateway wallet: <span class="font-semibold {{ $gatewayBalance <= 0 ? 'text-red-700' : ($gatewayBalance < 5 ? 'text-amber-700' : 'text-emerald-700') }}">{{ number_format($gatewayBalance, 2) }}</span>
                    </p>
                @endif
            </div>
        </div>

        {{-- Status chips: counts for the current filters; click to filter --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ $chipUrl(null) }}"
               class="rounded-lg border px-4 py-2 text-sm {{ empty($filters['status']) ? 'border-primary bg-primary/5 font-semibold text-primary' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300' }}">
                All <span class="ml-1 font-semibold">{{ number_format($total) }}</span>
            </a>
            @foreach(\App\Models\SmsMessage::STATUSES as $s)
                <a href="{{ $chipUrl($s) }}"
                   class="rounded-lg border px-4 py-2 text-sm {{ ($filters['status'] ?? null) === $s ? 'border-primary bg-primary/5 font-semibold text-primary' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300' }}">
                    {{ $s }} <span class="ml-1 font-semibold">{{ number_format($counts[$s]->n ?? 0) }}</span>
                    @if($s === 'Sent' && ($counts[$s]->credits ?? 0) > 0)
                        <span class="text-xs text-gray-500">· {{ number_format($counts[$s]->credits) }} credits</span>
                    @endif
                </a>
            @endforeach
        </div>

        <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-6 items-end">
            @if(!empty($filters['status']))<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-600">Search</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Phone, customer, text or message ID"
                       class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">Type</label>
                <select name="type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
                    <option value="">All types</option>
                    @foreach(\App\Models\SmsMessage::TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['type'] ?? null) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">From</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">To</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
            </div>
            <div class="flex gap-2">
                <button class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary">Filter</button>
                <a href="{{ route('sms_logs.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
            </div>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            @if($messages->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Time</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">To</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Message</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Credits</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        @foreach($messages as $m)
                            <tbody x-data="{ open: false }" class="border-t border-gray-100">
                                <tr class="hover:bg-gray-50 cursor-pointer align-top" @click="open = !open">
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                        {{ $m->created_at->format('M d, H:i') }}
                                        <div class="text-xs text-gray-400">{{ $m->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $m->phone }}</div>
                                        @if($m->customer)
                                            <a href="{{ route('customers.show', $m->customer_id) }}" @click.stop class="text-xs text-primary hover:underline">{{ $m->customer->name }}</a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                        {{ $m->type_label }}
                                        @if($m->sms_broadcast_id)
                                            <a href="{{ route('sms_broadcasts.show', $m->sms_broadcast_id) }}" @click.stop class="block text-xs text-primary hover:underline">Broadcast #{{ $m->sms_broadcast_id }}</a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 max-w-md">
                                        <div :class="open ? 'whitespace-pre-line' : 'truncate'">{{ $m->message }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $badge($m->status) }}">{{ $m->status }}</span>
                                        @if($m->error)
                                            <div class="mt-1 max-w-[16rem] text-xs text-red-600" :class="open ? '' : 'truncate'">{{ $m->error }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $m->credits ?: '—' }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        @if(in_array($m->status, ['Failed', 'Skipped'], true) && !$m->sms_broadcast_id)
                                            <form method="POST" action="{{ route('sms_logs.retry', $m) }}" @click.stop onsubmit="return confirm('Resend this SMS to {{ $m->phone }}?')">
                                                @csrf
                                                <button class="rounded-md border border-gray-300 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50">Resend</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                <tr x-cloak x-show="open" class="bg-gray-50/60">
                                    <td colspan="7" class="px-4 py-3 text-xs text-gray-600">
                                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                                            <div><span class="font-semibold">Queued:</span> {{ $m->created_at->format('M d, Y H:i:s') }}</div>
                                            <div><span class="font-semibold">Sent:</span> {{ $m->sent_at?->format('M d, Y H:i:s') ?? '—' }}</div>
                                            <div><span class="font-semibold">Attempts:</span> {{ $m->attempts }}</div>
                                            <div><span class="font-semibold">Gateway HTTP:</span> {{ $m->gateway_status ?? '—' }}</div>
                                            <div class="sm:col-span-2 break-all"><span class="font-semibold">Gateway message ID:</span> {{ $m->gateway_message_id ?? '—' }}</div>
                                            <div><span class="font-semibold">Gateway status:</span> {{ $m->gateway_state ?? '—' }}</div>
                                            <div><span class="font-semibold">Gateway cost:</span> {{ $m->gateway_cost !== null ? rtrim(rtrim(number_format((float) $m->gateway_cost, 4), '0'), '.') : '—' }}</div>
                                        </div>
                                        @if($m->gateway_response)
                                            <div class="mt-2"><span class="font-semibold">Gateway response:</span>
                                                <code class="block mt-1 whitespace-pre-wrap break-all rounded bg-white p-2 border border-gray-200">{{ $m->gateway_response }}</code>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        @endforeach
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-100">{{ $messages->links() }}</div>
            @else
                <div class="p-8 text-center text-sm text-gray-500">No SMS match these filters.</div>
            @endif
        </div>
    </div>
</x-app-layout>
