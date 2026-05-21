<div class="px-4 py-6 sm:p-8">
    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
        <div class="sm:col-span-4">
            <label class="block text-sm font-medium text-gray-900">Service <span class="text-red-500">*</span></label>
            <select name="platform_service_id" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                <option value="">Select a service</option>
                @foreach($services as $s)
                    <option value="{{ $s->id }}" @selected(old('platform_service_id', $subscription->platform_service_id ?? null) == $s->id)>{{ $s->name }} — {{ $s->currency }} {{ number_format($s->unit_price, 2) }} {{ $s->billing_cycle }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Quantity <span class="text-red-500">*</span></label>
            <input type="number" name="quantity" min="1" required value="{{ old('quantity', $subscription->quantity ?? 1) }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Unit Price Override</label>
            <input type="number" step="0.01" min="0" name="unit_price" placeholder="leave blank to use catalog" value="{{ old('unit_price', $subscription->unit_price ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
            <p class="text-xs text-gray-500 mt-1">Useful for grandfathered pricing.</p>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Status <span class="text-red-500">*</span></label>
            <select name="status" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                @foreach(['Active','Paused','Suspended','Cancelled'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $subscription->status ?? 'Active') === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Grace Days</label>
            <input type="number" name="grace_days" min="0" max="90" value="{{ old('grace_days', $subscription->grace_days ?? 7) }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Start Date <span class="text-red-500">*</span></label>
            <input type="date" name="start_date" required value="{{ old('start_date', isset($subscription) ? $subscription->start_date->format('Y-m-d') : date('Y-m-d')) }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Next Billing Date</label>
            <input type="date" name="next_billing_date" value="{{ old('next_billing_date', isset($subscription) ? $subscription->next_billing_date->format('Y-m-d') : '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
            <p class="text-xs text-gray-500 mt-1">Defaults to Start Date if blank.</p>
        </div>

        <div class="sm:col-span-3 flex items-center gap-2 pt-7">
            <input type="hidden" name="auto_renew" value="0">
            <input type="checkbox" name="auto_renew" value="1" id="autoRenewChk" @checked(old('auto_renew', $subscription->auto_renew ?? true)) class="h-4 w-4 rounded text-primary focus:ring-primary">
            <label for="autoRenewChk" class="text-sm text-gray-900">Auto-renew (generate invoice each cycle)</label>
        </div>
        <div class="sm:col-span-3 flex items-center gap-2 pt-7">
            <input type="hidden" name="force_payment" value="0">
            <input type="checkbox" name="force_payment" value="1" id="forceChk" @checked(old('force_payment', $subscription->force_payment ?? false)) class="h-4 w-4 rounded text-red-600 focus:ring-red-600">
            <label for="forceChk" class="text-sm font-semibold text-red-700">Force payment (block customer access if overdue past grace)</label>
        </div>

        <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-900">Notes</label>
            <textarea name="notes" rows="2"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">{{ old('notes', $subscription->notes ?? '') }}</textarea>
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
    <a href="{{ route('platform.subscriptions.index') }}" class="text-sm font-semibold text-gray-900">Cancel</a>
    <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">Save</button>
</div>
