<x-app-layout>
    @section('header') {{ $broadcast ? 'Edit Broadcast #' . $broadcast->id : 'New SMS Broadcast' }} @endsection

    @php
        $f = $broadcast?->filters ?? [];
        $selAudience  = old('audience', $f['audience'] ?? 'all');
        $selZones     = array_map('intval', old('zone_ids', $f['zone_ids'] ?? []));
        $selCustomers = array_map('intval', old('customer_ids', $f['customer_ids'] ?? []));
        $selTypes     = old('types', $f['types'] ?? []);
        $selDebtors   = (bool) old('debtors_only', $f['debtors_only'] ?? false);
        $selInactive  = (bool) old('include_inactive', $f['include_inactive'] ?? false);
        $selMessage   = old('message', $broadcast?->message);
        $selSchedule  = old('scheduled_at', $broadcast?->scheduled_at?->format('Y-m-d\TH:i'));
        $isScheduling = old('action', $broadcast?->status === 'Scheduled' ? 'schedule' : null) === 'schedule' || ($broadcast?->status === 'Scheduled' && !old('action'));
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">

        @if(session('error'))
            <div class="rounded-md bg-red-50 p-4 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-md bg-red-50 p-4 text-sm text-red-700 border border-red-200">
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @if($bundle)
            <div class="rounded-md bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">
                <span class="font-semibold">{{ number_format($bundle->remaining) }}</span> SMS credit{{ $bundle->remaining === 1 ? '' : 's' }} available — expires {{ $bundle->period_end->format('M d, Y') }}.
                1 credit = 1 SMS of up to 160 characters.
            </div>
        @else
            <div class="rounded-md bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                No active SMS bundle. <a href="{{ route('my.sms.index') }}" class="underline font-semibold">Buy SMS credits</a> before broadcasting.
            </div>
        @endif

        <form id="broadcast-form" action="{{ $broadcast ? route('sms_broadcasts.update', $broadcast) : route('sms_broadcasts.store') }}" method="POST" class="space-y-6">
            @csrf
            @if($broadcast) @method('PUT') @endif

            <!-- Audience -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-base font-semibold text-gray-900">1. Choose audience</h3>

                <div class="flex flex-wrap gap-4">
                    @foreach(['all' => 'All customers', 'zones' => 'By zone / area', 'customers' => 'Specific customers'] as $value => $label)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="radio" name="audience" value="{{ $value }}" {{ $selAudience === $value ? 'checked' : '' }}
                                   class="text-primary focus:ring-primary audience-radio">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                <!-- Zone picker -->
                <div id="zone-picker" class="hidden border-t border-gray-100 pt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Zones</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @forelse($zones as $zone)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="zone_ids[]" value="{{ $zone->id }}"
                                       {{ in_array($zone->id, $selZones) ? 'checked' : '' }}
                                       class="rounded text-primary focus:ring-primary filter-input">
                                {{ $zone->name }}
                            </label>
                        @empty
                            <p class="text-sm text-gray-500 col-span-full">No active zones defined.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Customer picker -->
                <div id="customer-picker" class="hidden border-t border-gray-100 pt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Customers</p>
                    <input type="text" id="customer-search" placeholder="Search by name or phone…"
                           class="w-full rounded-md border-0 py-1.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 mb-2">
                    <div class="max-h-56 overflow-y-auto border border-gray-200 rounded-md divide-y divide-gray-100" id="customer-list">
                        @foreach($customers as $customer)
                            <label class="flex items-center gap-2 text-sm text-gray-700 px-3 py-1.5 customer-row" data-search="{{ strtolower($customer->name . ' ' . $customer->phone) }}">
                                <input type="checkbox" name="customer_ids[]" value="{{ $customer->id }}"
                                       {{ in_array($customer->id, $selCustomers) ? 'checked' : '' }}
                                       class="rounded text-primary focus:ring-primary filter-input">
                                <span class="flex-1">{{ $customer->name }}</span>
                                <span class="text-xs text-gray-400">{{ $customer->phone }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Extra filters -->
                <div class="border-t border-gray-100 pt-4 flex flex-wrap gap-6">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Customer type</p>
                        <div class="flex gap-4">
                            @foreach(['Residential', 'Commercial'] as $type)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="types[]" value="{{ $type }}"
                                           {{ in_array($type, $selTypes) ? 'checked' : '' }}
                                           class="rounded text-primary focus:ring-primary filter-input">
                                    {{ $type }}
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Leave both unticked to include all types.</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Options</p>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="debtors_only" value="1" {{ $selDebtors ? 'checked' : '' }}
                                   class="rounded text-primary focus:ring-primary filter-input">
                            Only customers with outstanding balance
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 mt-1">
                            <input type="checkbox" name="include_inactive" value="1" {{ $selInactive ? 'checked' : '' }}
                                   class="rounded text-primary focus:ring-primary filter-input">
                            Include inactive customers
                        </label>
                    </div>
                </div>
            </div>

            <!-- Message -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h3 class="text-base font-semibold text-gray-900">2. Write message</h3>
                <textarea name="message" id="message" rows="4" maxlength="640" required
                          class="w-full rounded-md border-0 py-2 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-primary"
                          placeholder="Hello {name}, ...">{{ $selMessage }}</textarea>
                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                    <div>
                        Placeholders:
                        <button type="button" class="placeholder-btn font-mono bg-gray-100 rounded px-1.5 py-0.5 hover:bg-gray-200" data-ph="{name}">{name}</button>
                        <button type="button" class="placeholder-btn font-mono bg-gray-100 rounded px-1.5 py-0.5 hover:bg-gray-200" data-ph="{balance}">{balance}</button>
                        <span class="ml-1">— replaced per customer (e.g. "Hello {name}, your balance is {balance}").</span>
                    </div>
                    <div><span id="char-count">0</span> chars · <span id="msg-credits">1</span> credit(s)/message</div>
                </div>
                @error('message')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Delivery -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h3 class="text-base font-semibold text-gray-900">3. Delivery</h3>

                <label class="flex items-center gap-2 text-sm font-medium text-gray-900">
                    <input type="checkbox" id="schedule-toggle" {{ $isScheduling ? 'checked' : '' }}
                           class="rounded text-primary focus:ring-primary h-4 w-4">
                    Schedule for later
                </label>

                <div id="schedule-fields" class="hidden">
                    <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Scheduled Time</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                           value="{{ $selSchedule }}" min="{{ now()->addMinutes(2)->format('Y-m-d\TH:i') }}"
                           class="mt-1 block w-full sm:w-72 rounded-md border-0 py-1.5 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-primary">
                    <p class="text-xs text-gray-500 mt-1">Select date and time for scheduled delivery. Recipients and credits are re-checked at send time.</p>
                    @error('scheduled_at')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="border-t border-gray-100 pt-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="text-sm text-gray-700" id="estimate-box">
                        <span class="font-semibold" id="est-recipients">—</span> recipient(s) ·
                        <span class="font-semibold" id="est-credits">—</span> credit(s) needed
                        <span id="est-warning" class="hidden text-red-600 font-medium ml-2">Not enough credits!</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ $broadcast ? route('sms_broadcasts.show', $broadcast) : route('sms_broadcasts.index') }}"
                           class="rounded-md bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Close</a>
                        <button type="submit" name="action" value="draft"
                                class="rounded-md bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Save Draft
                        </button>
                        <button type="submit" name="action" value="send" id="send-btn"
                                onclick="return confirm('Send this broadcast now? This will use SMS credits.')"
                                class="rounded-md bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-secondary disabled:opacity-50">
                            Send Now
                        </button>
                        <button type="submit" name="action" value="schedule" id="schedule-btn"
                                class="hidden rounded-md bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-secondary disabled:opacity-50">
                            ⏱ Schedule
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        (function () {
            const form = document.getElementById('broadcast-form');
            const message = document.getElementById('message');
            const scheduleToggle = document.getElementById('schedule-toggle');

            function toggleSections() {
                const audience = form.querySelector('input[name="audience"]:checked').value;
                document.getElementById('zone-picker').classList.toggle('hidden', audience !== 'zones');
                document.getElementById('customer-picker').classList.toggle('hidden', audience !== 'customers');
            }

            function toggleSchedule() {
                const on = scheduleToggle.checked;
                document.getElementById('schedule-fields').classList.toggle('hidden', !on);
                document.getElementById('send-btn').classList.toggle('hidden', on);
                document.getElementById('schedule-btn').classList.toggle('hidden', !on);
            }

            function updateCharCount() {
                const len = message.value.length;
                document.getElementById('char-count').textContent = len;
                document.getElementById('msg-credits').textContent = Math.max(1, Math.ceil(len / 160));
            }

            let timer = null;
            function scheduleEstimate() {
                clearTimeout(timer);
                timer = setTimeout(fetchEstimate, 450);
            }

            async function fetchEstimate() {
                const data = new FormData(form);
                data.delete('action');
                data.delete('scheduled_at');
                try {
                    const res = await fetch('{{ route('sms_broadcasts.estimate') }}', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: data,
                    });
                    if (!res.ok) return;
                    const json = await res.json();
                    document.getElementById('est-recipients').textContent = json.recipients.toLocaleString();
                    document.getElementById('est-credits').textContent = json.credits_needed.toLocaleString();
                    const short = json.sufficient === false;
                    document.getElementById('est-warning').classList.toggle('hidden', !short);
                    document.getElementById('send-btn').disabled = short || json.recipients === 0;
                    document.getElementById('schedule-btn').disabled = json.recipients === 0;
                } catch (e) { /* estimate is advisory; never block typing */ }
            }

            form.querySelectorAll('.audience-radio').forEach(r => r.addEventListener('change', () => { toggleSections(); scheduleEstimate(); }));
            form.querySelectorAll('.filter-input').forEach(i => i.addEventListener('change', scheduleEstimate));
            message.addEventListener('input', () => { updateCharCount(); scheduleEstimate(); });
            scheduleToggle.addEventListener('change', toggleSchedule);

            document.querySelectorAll('.placeholder-btn').forEach(btn => btn.addEventListener('click', () => {
                const start = message.selectionStart || message.value.length;
                message.value = message.value.slice(0, start) + btn.dataset.ph + message.value.slice(message.selectionEnd || start);
                message.dispatchEvent(new Event('input'));
                message.focus();
            }));

            const search = document.getElementById('customer-search');
            if (search) search.addEventListener('input', () => {
                const q = search.value.trim().toLowerCase();
                document.querySelectorAll('.customer-row').forEach(row => {
                    row.classList.toggle('hidden', q !== '' && !row.dataset.search.includes(q));
                });
            });

            toggleSections();
            toggleSchedule();
            updateCharCount();
            fetchEstimate();
        })();
    </script>
</x-app-layout>
