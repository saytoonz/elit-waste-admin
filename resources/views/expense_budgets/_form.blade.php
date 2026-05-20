<div class="px-4 py-6 sm:p-8">
    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Category <span class="text-red-500">*</span></label>
            <select name="expense_category_id" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                <option value="">Select category</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('expense_category_id', $budget->expense_category_id ?? null) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Period <span class="text-red-500">*</span></label>
            <select name="period" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                @foreach(['Monthly','Quarterly','Yearly'] as $p)
                    <option value="{{ $p }}" @selected(old('period', $budget->period ?? 'Monthly') === $p)>{{ $p }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Year <span class="text-red-500">*</span></label>
            <input type="number" name="year" required min="2020" max="2100" value="{{ old('year', $budget->year ?? date('Y')) }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Month / Quarter Anchor</label>
            <select name="month" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                <option value="">— Required for Monthly / Quarterly —</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected(old('month', $budget->month ?? null) == $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                @endfor
            </select>
            <p class="text-xs text-gray-500 mt-1">For Quarterly, the system derives the quarter from this month.</p>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Amount (GHS) <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" min="0" name="amount" required value="{{ old('amount', $budget->amount ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>

        <div class="sm:col-span-3 flex items-end">
            <label class="inline-flex items-center gap-2 text-sm text-gray-900">
                <input type="checkbox" name="alert_enabled" value="1" @checked(old('alert_enabled', $budget->alert_enabled ?? true)) class="h-4 w-4 rounded text-primary focus:ring-primary">
                Show warnings as spending approaches limit
            </label>
        </div>

        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Alert Threshold (%)</label>
            <input type="number" name="alert_threshold_percent" min="1" max="100" value="{{ old('alert_threshold_percent', $budget->alert_threshold_percent ?? 80) }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>

        <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-900">Notes</label>
            <textarea name="notes" rows="2"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">{{ old('notes', $budget->notes ?? '') }}</textarea>
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
    <a href="{{ route('expense_budgets.index') }}" class="text-sm font-semibold text-gray-900">Cancel</a>
    <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">Save Budget</button>
</div>
