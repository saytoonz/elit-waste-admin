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

        <div>
            <a href="{{ route('sms_broadcasts.index') }}" class="text-sm text-primary hover:underline">← Back to broadcasts</a>
        </div>
    </div>
</x-app-layout>
