@php $input = 'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm'; @endphp
<x-app-layout>
    @section('header')
        Website
    @endsection

    @include('website.partials.nav')

    <div class="mb-6">
        <a href="{{ route('website.leads.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700">&larr; All leads</a>
        <div class="mt-1 flex flex-wrap items-center gap-3">
            <h1 class="text-xl font-bold text-gray-900">{{ $lead->name }}</h1>
            <span class="inline-flex rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $lead->status_badge }}">{{ $lead->status }}</span>
            <span class="text-sm text-gray-500">{{ $lead->type_label }} · {{ $lead->created_at->format('M j, Y g:ia') }}</span>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Details --}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <dl class="grid gap-5 sm:grid-cols-2">
                    @foreach([
                        'Phone' => $lead->phone, 'Email' => $lead->email, 'Business' => $lead->company, 'Location' => $lead->location,
                        'Customer type / interest' => $lead->service_interest, 'Frequency' => $lead->frequency,
                    ] as $label => $value)
                        @if($value)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($label === 'Phone')
                                        <a href="tel:{{ $value }}" class="font-semibold text-primary">{{ $value }}</a>
                                        <a href="https://wa.me/{{ preg_replace('/\D/', '', preg_replace('/^0/', '233', $value)) }}" target="_blank" class="ml-2 text-xs font-semibold text-green-600">WhatsApp</a>
                                    @elseif($label === 'Email')
                                        <a href="mailto:{{ $value }}" class="font-semibold text-primary">{{ $value }}</a>
                                    @else
                                        {{ $value }}
                                    @endif
                                </dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
                @if($lead->message)
                    <div class="mt-6 border-t pt-5">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">Message</dt>
                        <dd class="mt-2 whitespace-pre-line text-sm text-gray-800">{{ $lead->message }}</dd>
                    </div>
                @endif
                @if($lead->source_url)
                    <p class="mt-5 text-xs text-gray-400">Sent from {{ $lead->source_url }}</p>
                @endif
            </div>

            {{-- Activity --}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h2 class="text-base font-semibold text-gray-900">Activity</h2>
                <form method="POST" action="{{ route('website.leads.activities.store', $lead) }}" class="mt-4">
                    @csrf
                    <div class="flex gap-3">
                        <select name="type" class="{{ $input }} w-36 shrink-0">
                            @foreach(\App\Models\Website\WebsiteLeadActivity::TYPES as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                        </select>
                        <textarea name="body" rows="2" required placeholder="What happened? e.g. Called — wants weekly collection, deliver bin Friday." class="{{ $input }}"></textarea>
                    </div>
                    <div class="mt-2 text-right"><button class="rounded-md bg-primary px-3 py-1.5 text-sm font-semibold text-white hover:bg-secondary">Log activity</button></div>
                </form>

                <ul class="mt-6 space-y-5">
                    @forelse($lead->activities as $a)
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $a->type === 'status' ? 'bg-gray-100 text-gray-500' : 'bg-primary/10 text-primary' }} text-xs font-bold uppercase">{{ mb_substr($a->type, 0, 1) }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $a->body }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">{{ \App\Models\Website\WebsiteLeadActivity::TYPES[$a->type] ?? 'Update' }} · {{ $a->user?->name ?? 'System' }} · {{ $a->created_at->diffForHumans() }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">No activity yet. Log your first call or note above.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-6">
            <form method="POST" action="{{ route('website.leads.update', $lead) }}" class="space-y-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                @csrf @method('PATCH')
                <h2 class="text-base font-semibold text-gray-900">Pipeline</h2>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-900">Status</label>
                    <select name="status" class="{{ $input }}">
                        @foreach(\App\Models\Website\WebsiteLead::STATUSES as $st)<option @selected($lead->status === $st)>{{ $st }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-900">Assigned to</label>
                    <select name="assigned_to" class="{{ $input }}">
                        <option value="">Nobody</option>
                        @foreach($users as $u)<option value="{{ $u->id }}" @selected((int) $lead->assigned_to === (int) $u->id)>{{ $u->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-900">Follow up on</label>
                    <input type="date" name="follow_up_at" value="{{ optional($lead->follow_up_at)->toDateString() }}" class="{{ $input }}">
                </div>
                <button class="w-full rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary">Save</button>
            </form>

            <div class="space-y-3 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h2 class="text-base font-semibold text-gray-900">Customer</h2>
                @if($lead->customer)
                    <p class="text-sm text-gray-700">Converted to <a href="{{ route('customers.show', $lead->customer) }}" class="font-semibold text-primary">{{ $lead->customer->name }}</a>.</p>
                @else
                    <p class="text-sm text-gray-600">Ready to sign them up? This opens the New Customer form with their details filled in.</p>
                    <form method="POST" action="{{ route('website.leads.convert', $lead) }}">@csrf
                        <button class="w-full rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500">Convert to customer</button>
                    </form>
                @endif
            </div>

            <form method="POST" action="{{ route('website.leads.destroy', $lead) }}" onsubmit="return confirm('Delete this lead and its activity?')">
                @csrf @method('DELETE')
                <button class="text-sm font-semibold text-red-600 hover:text-red-500">Delete lead</button>
            </form>
        </div>
    </div>
</x-app-layout>
