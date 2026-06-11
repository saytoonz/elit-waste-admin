<x-app-layout>
    @section('header') Broadcast #{{ $broadcast->id }} @endsection

    <div class="max-w-4xl mx-auto space-y-6">

        @if(session('success'))
            <div class="rounded-md bg-green-50 p-4 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-md bg-red-50 p-4 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">SMS Broadcast</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $broadcast->created_at->format('M d, Y H:i') }}
                        @if($broadcast->creator) · by {{ $broadcast->creator->name }} @endif
                    </p>
                    @if($broadcast->status === 'Scheduled' && $broadcast->scheduled_at)
                        <p class="text-sm text-indigo-700 font-medium mt-1">⏱ Scheduled for {{ $broadcast->scheduled_at->format('M d, Y H:i') }}</p>
                    @endif
                </div>
                @php
                    $badge = match ($broadcast->status) {
                        'Completed'  => 'bg-green-50 text-green-700 ring-green-600/20',
                        'Processing' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                        'Scheduled'  => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                        'Draft'      => 'bg-gray-50 text-gray-700 ring-gray-600/20',
                        'Failed'     => 'bg-red-50 text-red-700 ring-red-600/20',
                        default      => 'bg-gray-50 text-gray-700 ring-gray-600/20',
                    };
                @endphp
                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $badge }}">
                    {{ $broadcast->status }}
                </span>
            </div>

            @if($broadcast->status === 'Failed' && $broadcast->failure_reason)
                <div class="rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                    {{ $broadcast->failure_reason }} — fix the issue (e.g. top up credits), then edit or send again.
                </div>
            @endif

            @if($broadcast->isEditable())
                <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-4">
                    <a href="{{ route('sms_broadcasts.edit', $broadcast) }}"
                       class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Edit</a>
                    <form action="{{ route('sms_broadcasts.sendNow', $broadcast) }}" method="POST"
                          onsubmit="return confirm('Send this broadcast now? This will use SMS credits.')">
                        @csrf
                        <button class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary">Send Now</button>
                    </form>
                    <form action="{{ route('sms_broadcasts.destroy', $broadcast) }}" method="POST" class="ml-auto"
                          onsubmit="return confirm('Delete this broadcast? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-red-600 ring-1 ring-inset ring-red-200 hover:bg-red-50">Delete</button>
                    </form>
                </div>
            @endif

            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Audience</p>
                <p class="text-sm text-gray-700">{{ $broadcast->audience_summary }}</p>
            </div>

            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Message</p>
                <p class="text-sm text-gray-900 whitespace-pre-wrap bg-gray-50 rounded-md p-3 border border-gray-100">{{ $broadcast->message }}</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 pt-2 border-t border-gray-100">
                <div>
                    <div class="text-xs uppercase text-gray-500">Recipients</div>
                    <div class="text-xl font-bold text-gray-900 mt-1">{{ number_format($broadcast->recipients_count) }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500">Sent</div>
                    <div class="text-xl font-bold text-emerald-700 mt-1">{{ number_format($broadcast->sent_count) }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500">Failed</div>
                    <div class="text-xl font-bold {{ $broadcast->failed_count > 0 ? 'text-red-700' : 'text-gray-900' }} mt-1">{{ number_format($broadcast->failed_count) }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500">Skipped</div>
                    <div class="text-xl font-bold {{ $broadcast->skipped_count > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ number_format($broadcast->skipped_count) }}</div>
                </div>
                <div>
                    <div class="text-xs uppercase text-gray-500">Credits Used</div>
                    <div class="text-xl font-bold text-gray-900 mt-1">{{ number_format($broadcast->credits_used) }} <span class="text-sm font-normal text-gray-400">/ ~{{ number_format($broadcast->credits_estimated) }}</span></div>
                </div>
            </div>

            @if($broadcast->status === 'Processing')
                <div>
                    <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-2 bg-blue-500" style="width: {{ min(100, $broadcast->progress_percent) }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ number_format($broadcast->processed_count) }} of {{ number_format($broadcast->recipients_count) }} processed — this page refreshes every 10 seconds.</p>
                </div>
                <script>setTimeout(() => window.location.reload(), 10000);</script>
            @endif

            @if($broadcast->skipped_count > 0)
                <p class="text-xs text-amber-700">Skipped = blocked by SMS credit quota (bundle exhausted, expired, or message didn't fit remaining credits).</p>
            @endif
        </div>

        <!-- Recipients -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h3 class="text-base font-semibold text-gray-900">Recipients</h3>
                @if($broadcast->failed_count + $broadcast->skipped_count > 0)
                    <form action="{{ route('sms_broadcasts.retryFailed', $broadcast) }}" method="POST"
                          onsubmit="return confirm('Retry all failed and skipped recipients? Successful sends will use SMS credits.')">
                        @csrf
                        <button class="rounded-md bg-amber-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-amber-500">
                            ↻ Retry Failed & Skipped ({{ number_format($broadcast->failed_count + $broadcast->skipped_count) }})
                        </button>
                    </form>
                @endif
            </div>

            @if($recipients->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="py-2 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Customer</th>
                                <th class="py-2 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Phone</th>
                                <th class="py-2 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Message</th>
                                <th class="py-2 pr-3 text-right text-xs font-semibold text-gray-700 uppercase">Credits</th>
                                <th class="py-2 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                <th class="py-2 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Sent At</th>
                                <th class="py-2 text-right text-xs font-semibold text-gray-700 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recipients as $r)
                                <tr>
                                    <td class="py-2 pr-3 text-gray-900">
                                        @if($r->customer_id)
                                            <a href="{{ route('customers.show', $r->customer_id) }}" class="hover:underline">{{ $r->name }}</a>
                                        @else
                                            {{ $r->name ?? '—' }}
                                        @endif
                                    </td>
                                    <td class="py-2 pr-3 text-gray-600 whitespace-nowrap">{{ $r->phone }}</td>
                                    <td class="py-2 pr-3 text-gray-600 max-w-xs truncate" title="{{ $r->message }}">{{ \Illuminate\Support\Str::limit($r->message, 50) }}</td>
                                    <td class="py-2 pr-3 text-right">{{ $r->credits > 0 ? number_format($r->credits) : '—' }}</td>
                                    <td class="py-2 pr-3">
                                        @php
                                            $rBadge = match ($r->status) {
                                                'Sent'    => 'bg-green-50 text-green-700 ring-green-600/20',
                                                'Failed'  => 'bg-red-50 text-red-700 ring-red-600/20',
                                                'Skipped' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                default   => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $rBadge }}">{{ $r->status }}</span>
                                        @if($r->error)<div class="text-xs text-gray-500 mt-0.5 max-w-[16rem]">{{ $r->error }}</div>@endif
                                    </td>
                                    <td class="py-2 pr-3 text-gray-500 whitespace-nowrap text-xs">{{ $r->sent_at?->format('M d, H:i:s') ?? '—' }}</td>
                                    <td class="py-2 text-right">
                                        @if($r->isRetryable())
                                            <form action="{{ route('sms_broadcasts.retryRecipient', [$broadcast, $r]) }}" method="POST" class="inline">
                                                @csrf
                                                <button class="rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-300 hover:bg-amber-50">↻ Retry</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $recipients->links() }}</div>
            @else
                <p class="text-sm text-gray-500">
                    @if(in_array($broadcast->status, ['Draft', 'Scheduled']))
                        Recipients are resolved when the broadcast is sent.
                    @else
                        No per-recipient log for this broadcast (sent before delivery tracking was added).
                    @endif
                </p>
            @endif
        </div>

        <div>
            <a href="{{ route('sms_broadcasts.index') }}" class="text-sm text-primary hover:underline">← Back to broadcasts</a>
        </div>
    </div>
</x-app-layout>
