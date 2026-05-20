<div class="px-4 py-6 sm:p-8">
    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">

        <div class="col-span-full">
            <h2 class="text-base font-semibold leading-7 text-gray-900">Classification</h2>
        </div>

        <div class="sm:col-span-3">
            <label for="expense_category_id" class="block text-sm font-medium text-gray-900">Category <span class="text-red-500">*</span></label>
            <select id="expense_category_id" name="expense_category_id" required
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                <option value="">Select category</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('expense_category_id', $expense->expense_category_id ?? null) == $c->id)>{{ $c->parent ? $c->parent->name . ' / ' : '' }}{{ $c->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('expense_category_id')" class="mt-2" />
        </div>

        <div class="sm:col-span-3">
            <label for="vendor_id" class="block text-sm font-medium text-gray-900">Vendor / Payee</label>
            <select id="vendor_id" name="vendor_id"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                <option value="">— None —</option>
                @foreach($vendors as $v)
                    <option value="{{ $v->id }}" @selected(old('vendor_id', $expense->vendor_id ?? null) == $v->id)>{{ $v->name }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500"><a href="{{ route('vendors.create') }}" class="text-primary hover:underline">+ Add new vendor</a></p>
        </div>

        <div class="sm:col-span-3">
            <label for="zone_id" class="block text-sm font-medium text-gray-900">Zone (optional)</label>
            <select id="zone_id" name="zone_id"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                <option value="">All / N/A</option>
                @foreach($zones as $z)
                    <option value="{{ $z->id }}" @selected(old('zone_id', $expense->zone_id ?? null) == $z->id)>{{ $z->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-3">
            <label for="expense_date" class="block text-sm font-medium text-gray-900">Expense Date <span class="text-red-500">*</span></label>
            <input type="date" name="expense_date" id="expense_date" required
                value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : date('Y-m-d')) }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
            <x-input-error :messages="$errors->get('expense_date')" class="mt-2" />
        </div>

        <div class="col-span-full border-t border-gray-900/10"></div>

        <div class="col-span-full">
            <h2 class="text-base font-semibold leading-7 text-gray-900">Amount</h2>
        </div>

        <div class="sm:col-span-2">
            <label for="amount" class="block text-sm font-medium text-gray-900">Amount (GHS) <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" min="0" name="amount" id="amount" required
                value="{{ old('amount', $expense->amount ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm"
                x-data x-on:input="$dispatch('amount-changed', $event.target.value)">
            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
        </div>

        <div class="sm:col-span-2">
            <label for="tax_amount" class="block text-sm font-medium text-gray-900">Tax (GHS)</label>
            <input type="number" step="0.01" min="0" name="tax_amount" id="tax_amount"
                value="{{ old('tax_amount', $expense->tax_amount ?? '0') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Total</label>
            <div class="mt-2 flex items-center h-9 px-3 rounded-md bg-gray-100 text-gray-900 font-semibold" id="totalDisplay">
                GHS {{ number_format(old('amount', $expense->amount ?? 0) + old('tax_amount', $expense->tax_amount ?? 0), 2) }}
            </div>
        </div>

        <div class="sm:col-span-3">
            <label for="payment_method" class="block text-sm font-medium text-gray-900">Payment Method <span class="text-red-500">*</span></label>
            <select id="payment_method" name="payment_method" required
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                @foreach(['Cash','Bank Transfer','Mobile Money','Card','Cheque','Other'] as $m)
                    <option value="{{ $m }}" @selected(old('payment_method', $expense->payment_method ?? 'Cash') == $m)>{{ $m }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-3">
            <label for="reference" class="block text-sm font-medium text-gray-900">Reference / Receipt #</label>
            <input type="text" name="reference" id="reference"
                value="{{ old('reference', $expense->reference ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>

        <div class="col-span-full border-t border-gray-900/10"></div>

        <div class="col-span-full">
            <label for="description" class="block text-sm font-medium text-gray-900">Description <span class="text-red-500">*</span></label>
            <textarea name="description" id="description" rows="2" required
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">{{ old('description', $expense->description ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="col-span-full">
            <label for="attachment" class="block text-sm font-medium text-gray-900">Receipt (PDF/JPG/PNG, max 5MB)</label>
            <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp"
                class="mt-2 block w-full text-sm text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-secondary">
            @if(!empty($expense->attachment_path ?? null))
                <p class="mt-1 text-xs text-gray-500">Current: <a href="{{ route('expenses.attachment', $expense) }}" class="text-primary hover:underline">{{ $expense->attachment_name }}</a></p>
            @endif
            <x-input-error :messages="$errors->get('attachment')" class="mt-2" />
        </div>

        <div class="col-span-full">
            <label for="notes" class="block text-sm font-medium text-gray-900">Internal Notes</label>
            <textarea name="notes" id="notes" rows="2"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">{{ old('notes', $expense->notes ?? '') }}</textarea>
        </div>

        @if(!isset($expense))
            <div class="col-span-full">
                <label class="flex items-center gap-2 text-sm text-gray-900">
                    <input type="radio" name="status" value="Pending" checked class="text-primary focus:ring-primary">
                    Submit for approval
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-900 mt-1">
                    <input type="radio" name="status" value="Draft" class="text-primary focus:ring-primary">
                    Save as draft
                </label>
            </div>
        @endif
    </div>
</div>

<div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
    <a href="{{ route('expenses.index') }}" class="text-sm font-semibold text-gray-900">Cancel</a>
    <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">Save Expense</button>
</div>

@push('scripts')
<script>
    (function() {
        const amount = document.getElementById('amount');
        const tax = document.getElementById('tax_amount');
        const total = document.getElementById('totalDisplay');
        const fmt = (n) => 'GHS ' + (n || 0).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const recalc = () => {
            const a = parseFloat(amount.value || 0);
            const t = parseFloat(tax.value || 0);
            total.textContent = fmt(a + t);
        };
        amount.addEventListener('input', recalc);
        tax.addEventListener('input', recalc);
    })();
</script>
@endpush
