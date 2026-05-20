<div class="px-4 py-6 sm:p-8">
    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
        <div class="sm:col-span-4">
            <label class="block text-sm font-medium text-gray-900">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" required placeholder="e.g. Monthly Office Rent" value="{{ old('name', $recurring->name ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Frequency <span class="text-red-500">*</span></label>
            <select name="frequency" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                @foreach(['Daily','Weekly','Monthly','Quarterly','Yearly'] as $f)
                    <option value="{{ $f }}" @selected(old('frequency', $recurring->frequency ?? 'Monthly') === $f)>{{ $f }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Category <span class="text-red-500">*</span></label>
            <select name="expense_category_id" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                <option value="">Select category</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('expense_category_id', $recurring->expense_category_id ?? null) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Vendor</label>
            <select name="vendor_id" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                <option value="">— None —</option>
                @foreach($vendors as $v)
                    <option value="{{ $v->id }}" @selected(old('vendor_id', $recurring->vendor_id ?? null) == $v->id)>{{ $v->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Amount (GHS) <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" min="0" name="amount" required value="{{ old('amount', $recurring->amount ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Tax (GHS)</label>
            <input type="number" step="0.01" min="0" name="tax_amount" value="{{ old('tax_amount', $recurring->tax_amount ?? '0') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Default Payment Method <span class="text-red-500">*</span></label>
            <select name="payment_method" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                @foreach(['Cash','Bank Transfer','Mobile Money','Card','Cheque','Other'] as $m)
                    <option value="{{ $m }}" @selected(old('payment_method', $recurring->payment_method ?? 'Bank Transfer') === $m)>{{ $m }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Start Date <span class="text-red-500">*</span></label>
            <input type="date" name="start_date" required value="{{ old('start_date', isset($recurring) ? $recurring->start_date->format('Y-m-d') : date('Y-m-d')) }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">End Date (optional)</label>
            <input type="date" name="end_date" value="{{ old('end_date', isset($recurring) && $recurring->end_date ? $recurring->end_date->format('Y-m-d') : '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Next Run Date</label>
            <input type="date" name="next_run_date" value="{{ old('next_run_date', isset($recurring) ? $recurring->next_run_date->format('Y-m-d') : '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
            <p class="text-xs text-gray-500 mt-1">Defaults to Start Date if blank.</p>
        </div>

        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Zone (optional)</label>
            <select name="zone_id" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                <option value="">N/A</option>
                @foreach($zones as $z)
                    <option value="{{ $z->id }}" @selected(old('zone_id', $recurring->zone_id ?? null) == $z->id)>{{ $z->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-3 flex items-end gap-6">
            <label class="inline-flex items-center gap-2 text-sm text-gray-900">
                <input type="checkbox" name="auto_approve" value="1" @checked(old('auto_approve', $recurring->auto_approve ?? false)) class="h-4 w-4 rounded text-primary focus:ring-primary">
                Auto-approve generated expenses
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-gray-900">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $recurring->is_active ?? true)) class="h-4 w-4 rounded text-primary focus:ring-primary">
                Active
            </label>
        </div>

        <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-900">Description <span class="text-red-500">*</span></label>
            <textarea name="description" rows="2" required
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">{{ old('description', $recurring->description ?? '') }}</textarea>
        </div>

        <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-900">Notes</label>
            <textarea name="notes" rows="2"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">{{ old('notes', $recurring->notes ?? '') }}</textarea>
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
    <a href="{{ route('recurring_expenses.index') }}" class="text-sm font-semibold text-gray-900">Cancel</a>
    <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">Save Schedule</button>
</div>
