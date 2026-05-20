<div class="px-4 py-6 sm:p-8">
    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
        <div class="sm:col-span-4">
            <label class="block text-sm font-medium text-gray-900">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" required value="{{ old('name', $vendor->name ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="sm:col-span-2 flex items-end">
            <label class="inline-flex items-center gap-2 text-sm text-gray-900">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $vendor->is_active ?? true)) class="h-4 w-4 rounded text-primary focus:ring-primary">
                Active
            </label>
        </div>

        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Contact Person</label>
            <input type="text" name="contact_person" value="{{ old('contact_person', $vendor->contact_person ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>

        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $vendor->phone ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>

        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Email</label>
            <input type="email" name="email" value="{{ old('email', $vendor->email ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
        </div>

        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Tax / Registration ID</label>
            <input type="text" name="tax_id" value="{{ old('tax_id', $vendor->tax_id ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm font-mono">
        </div>

        <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-900">Address</label>
            <textarea name="address" rows="2"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">{{ old('address', $vendor->address ?? '') }}</textarea>
        </div>

        <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-900">Notes</label>
            <textarea name="notes" rows="2"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">{{ old('notes', $vendor->notes ?? '') }}</textarea>
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
    <a href="{{ route('vendors.index') }}" class="text-sm font-semibold text-gray-900">Cancel</a>
    <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">Save Vendor</button>
</div>
