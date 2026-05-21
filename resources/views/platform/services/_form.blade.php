<div class="px-4 py-6 sm:p-8">
    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
        <div class="sm:col-span-4">
            <label class="block text-sm font-medium text-gray-900">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" required value="{{ old('name', $service->name ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Type <span class="text-red-500">*</span></label>
            <select name="type" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                @foreach(['Email','Hosting','Domain','SMS','Storage','Other'] as $t)
                    <option value="{{ $t }}" @selected(old('type', $service->type ?? 'Other') === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Unit Price <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" min="0" name="unit_price" required value="{{ old('unit_price', $service->unit_price ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Currency <span class="text-red-500">*</span></label>
            <select name="currency" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                @foreach(['USD','GHS','NGN','KES','ZAR','EUR','GBP'] as $c)
                    <option value="{{ $c }}" @selected(old('currency', $service->currency ?? 'USD') === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Billing Cycle <span class="text-red-500">*</span></label>
            <select name="billing_cycle" required class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                @foreach(['Monthly','Quarterly','Yearly'] as $cy)
                    <option value="{{ $cy }}" @selected(old('billing_cycle', $service->billing_cycle ?? 'Monthly') === $cy)>{{ $cy }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2 flex items-center pt-7">
            <label class="inline-flex items-center gap-2 text-sm text-gray-900">
                <input type="hidden" name="is_quantity_based" value="0">
                <input type="checkbox" name="is_quantity_based" value="1" @checked(old('is_quantity_based', $service->is_quantity_based ?? false)) class="h-4 w-4 rounded text-primary focus:ring-primary">
                Price per unit (e.g. per email account)
            </label>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Unit Label</label>
            <input type="text" name="unit_label" placeholder="e.g. email account, 1000 SMS" value="{{ old('unit_label', $service->unit_label ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>
        <div class="sm:col-span-1">
            <label class="block text-sm font-medium text-gray-900">Default Qty</label>
            <input type="number" name="default_quantity" min="1" value="{{ old('default_quantity', $service->default_quantity ?? 1) }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>
        <div class="sm:col-span-1">
            <label class="block text-sm font-medium text-gray-900">Min Qty</label>
            <input type="number" name="min_quantity" min="1" value="{{ old('min_quantity', $service->min_quantity ?? 1) }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Grace Days After Due</label>
            <input type="number" name="grace_days" min="0" max="90" value="{{ old('grace_days', $service->grace_days ?? 7) }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
            <p class="text-xs text-gray-500 mt-1">After grace + force-payment flag, customer is blocked.</p>
        </div>
        <div class="sm:col-span-2 flex items-center pt-7">
            <label class="inline-flex items-center gap-2 text-sm text-gray-900">
                <input type="hidden" name="customer_addable" value="0">
                <input type="checkbox" name="customer_addable" value="1" @checked(old('customer_addable', $service->customer_addable ?? true)) class="h-4 w-4 rounded text-primary focus:ring-primary">
                Customer can self-subscribe / add more
            </label>
        </div>
        <div class="sm:col-span-2 flex items-center pt-7">
            <label class="inline-flex items-center gap-2 text-sm text-gray-900">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active ?? true)) class="h-4 w-4 rounded text-primary focus:ring-primary">
                Active
            </label>
        </div>

        <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-900">Description</label>
            <textarea name="description" rows="2"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">{{ old('description', $service->description ?? '') }}</textarea>
        </div>

        <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-900">Features (one per line)</label>
            <textarea name="features_raw" rows="4" placeholder="One feature per line, shown to customers"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">{{ old('features_raw', isset($service) && $service->features ? implode("\n", $service->features) : '') }}</textarea>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Slug</label>
            <input type="text" name="slug" placeholder="auto-generated if blank" value="{{ old('slug', $service->slug ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm font-mono">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Sort Order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order ?? 0) }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
    <a href="{{ route('platform.services.index') }}" class="text-sm font-semibold text-gray-900">Cancel</a>
    <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">Save Service</button>
</div>
