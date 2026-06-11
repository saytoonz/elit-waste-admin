<x-app-layout>
    @section('header') SMS Broadcasts @endsection

    <div class="max-w-6xl mx-auto space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                @if($bundle)
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold {{ $bundle->remaining === 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($bundle->remaining) }}</span>
                        credit{{ $bundle->remaining === 1 ? '' : 's' }} remaining · expires {{ $bundle->period_end->format('M d, Y') }}
                    </p>
                @else
                    <p class="text-sm text-amber-700 font-medium">No active SMS bundle.</p>
                @endif
            </div>
            <a href="{{ route('sms_broadcasts.create') }}" class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary">
                + New Broadcast
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            @if($broadcasts->isNotEmpty())
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Message</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Audience</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Recipients</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Sent</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Credits</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($broadcasts as $b)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('sms_broadcasts.show', $b) }}'">
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $b->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-4 py-3 text-gray-900 max-w-xs truncate">{{ \Illuminate\Support\Str::limit($b->message, 60) }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $b->audience_summary }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($b->recipients_count) }}</td>
                                <td class="px-4 py-3 text-right">
                                    {{ number_format($b->sent_count) }}
                                    @if($b->failed_count > 0)<span class="text-red-600 text-xs">({{ $b->failed_count }} failed)</span>@endif
                                    @if($b->skipped_count > 0)<span class="text-amber-600 text-xs">({{ $b->skipped_count }} skipped)</span>@endif
                                </td>
                                <td class="px-4 py-3 text-right">{{ number_format($b->credits_used) }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $badge = match ($b->status) {
                                            'Completed'  => 'bg-green-50 text-green-700 ring-green-600/20',
                                            'Processing' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                            'Scheduled'  => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                            'Draft'      => 'bg-gray-50 text-gray-700 ring-gray-600/20',
                                            'Failed'     => 'bg-red-50 text-red-700 ring-red-600/20',
                                            default      => 'bg-gray-50 text-gray-700 ring-gray-600/20',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $badge }}">
                                        {{ $b->status }}{{ $b->status === 'Processing' ? " ({$b->progress_percent}%)" : '' }}
                                    </span>
                                    @if($b->status === 'Scheduled' && $b->scheduled_at)
                                        <div class="text-xs text-gray-500 mt-0.5">⏱ {{ $b->scheduled_at->format('M d, H:i') }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t border-gray-100">{{ $broadcasts->links() }}</div>
            @else
                <div class="p-8 text-center text-sm text-gray-500">
                    No broadcasts yet. Send your first message to your customers.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
