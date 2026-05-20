<div class="px-4 py-6 sm:p-8">
    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
        <div class="sm:col-span-4">
            <label class="block text-sm font-medium text-gray-900">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" required value="{{ old('name', $category->name ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Code</label>
            <input type="text" name="code" value="{{ old('code', $category->code ?? '') }}"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm font-mono">
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="sm:col-span-3">
            <label class="block text-sm font-medium text-gray-900">Parent Category</label>
            <select name="parent_id" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                <option value="">— None (top level) —</option>
                @foreach($parents as $p)
                    <option value="{{ $p->id }}" @selected(old('parent_id', $category->parent_id ?? null) == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-900">Color</label>
            <input type="color" name="color" value="{{ old('color', $category->color ?? '#6B7280') }}"
                class="mt-2 h-9 w-full rounded-md border border-gray-300">
        </div>

        <div class="sm:col-span-1 flex items-end">
            <label class="inline-flex items-center gap-2 text-sm text-gray-900">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true)) class="h-4 w-4 rounded text-primary focus:ring-primary">
                Active
            </label>
        </div>

        <div class="col-span-full">
            <label class="block text-sm font-medium text-gray-900">Description</label>
            <textarea name="description" rows="2"
                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">{{ old('description', $category->description ?? '') }}</textarea>
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
    <a href="{{ route('expense_categories.index') }}" class="text-sm font-semibold text-gray-900">Cancel</a>
    <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">Save</button>
</div>
