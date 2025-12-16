<x-app-layout>
    @section('header')
        Edit Zone: {{ $zone->name }}
    @endsection

    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('zones.update', $zone) }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
            @csrf
            @method('PUT')
            <div class="px-4 py-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Zone Name</label>
                        <div class="mt-2">
                            <input type="text" name="name" id="name" value="{{ old('name', $zone->name) }}" required
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                    </div>

                    <div class="col-span-full">
                        <label for="description" class="block text-sm font-medium leading-6 text-gray-900">Description (Optional)</label>
                        <div class="mt-2">
                            <textarea id="description" name="description" rows="3" 
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">{{ old('description', $zone->description) }}</textarea>
                             <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                    </div>

                    <div class="col-span-full">
                        <div class="relative flex gap-x-3">
                            <div class="flex h-6 items-center">
                                <input id="is_active" name="is_active" type="checkbox" value="1" {{ $zone->is_active ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                            </div>
                            <div class="text-sm leading-6">
                                <label for="is_active" class="font-medium text-gray-900">Active</label>
                                <p class="text-gray-500">Uncheck to disable this zone temporarily.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                <a href="{{ route('zones.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                <button type="submit" class="rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Update Zone</button>
            </div>
        </form>
        
        <div class="mt-6 flex justify-end">
             <form method="POST" action="{{ route('zones.destroy', $zone) }}" onsubmit="return confirm('Are you sure you want to delete this zone?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800">Delete Zone</button>
            </form>
        </div>
    </div>
</x-app-layout>
