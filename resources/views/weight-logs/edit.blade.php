<x-app-layout>
    @section('header')
        Edit Weighing Record
    @endsection

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('weight-logs.update', $weightLog) }}"
              x-data="{ gross: {{ (float) old('gross_weight', $weightLog->gross_weight) }}, tare: {{ (float) old('tare_weight', $weightLog->tare_weight) }} }"
              class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
            @csrf
            @method('PUT')

            <div class="px-4 py-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">

                    <div class="col-span-full">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Trip Details</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">When the load arrived and which vehicle carried it.</p>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="logged_at" class="block text-sm font-medium leading-6 text-gray-900">Date &amp; Time Weighed</label>
                        <div class="mt-2">
                            <input type="datetime-local" name="logged_at" id="logged_at" required
                                value="{{ old('logged_at', $weightLog->logged_at->format('Y-m-d\TH:i')) }}"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('logged_at')" class="mt-2" />
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="vehicle_label" class="block text-sm font-medium leading-6 text-gray-900">Vehicle / Bike</label>
                        <div class="mt-2">
                            <input type="text" name="vehicle_label" id="vehicle_label" required
                                value="{{ old('vehicle_label', $weightLog->vehicle_label) }}" placeholder="e.g. GR-234-21 or Bike-07"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('vehicle_label')" class="mt-2" />
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="trash_type" class="block text-sm font-medium leading-6 text-gray-900">Type of Trash</label>
                        <div class="mt-2">
                            <select id="trash_type" name="trash_type"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:max-w-xs sm:text-sm sm:leading-6">
                                @foreach($trashTypes as $type)
                                    <option value="{{ $type }}" {{ old('trash_type', $weightLog->trash_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('trash_type')" class="mt-2" />
                        </div>
                    </div>

                    <div class="col-span-full border-t border-gray-900/10 my-4"></div>

                    <div class="col-span-full">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Weighing (kg)</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Net trash weight is calculated as Gross − Tare.</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="gross_weight" class="block text-sm font-medium leading-6 text-gray-900">Gross Weight (loaded)</label>
                        <div class="mt-2">
                            <input type="number" step="0.01" min="0" name="gross_weight" id="gross_weight" required
                                x-model.number="gross" value="{{ old('gross_weight', $weightLog->gross_weight) }}"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('gross_weight')" class="mt-2" />
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="tare_weight" class="block text-sm font-medium leading-6 text-gray-900">Tare Weight (empty vehicle)</label>
                        <div class="mt-2">
                            <input type="number" step="0.01" min="0" name="tare_weight" id="tare_weight" required
                                x-model.number="tare" value="{{ old('tare_weight', $weightLog->tare_weight) }}"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('tare_weight')" class="mt-2" />
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <span class="block text-sm font-medium leading-6 text-gray-900">Net Weight (trash)</span>
                        <div class="mt-2">
                            <div class="flex items-center h-[38px] rounded-md bg-gray-50 px-3 ring-1 ring-inset ring-gray-200">
                                <span class="text-lg font-bold text-gray-900" x-text="Math.max(0, (gross - tare)).toFixed(2)">0.00</span>
                                <span class="ml-1 text-sm text-gray-500">kg</span>
                            </div>
                            <p class="mt-1 text-xs" :class="tare > gross ? 'text-red-600' : 'text-gray-500'"
                               x-text="tare > gross ? 'Tare cannot exceed gross weight.' : 'Auto-calculated.'"></p>
                        </div>
                    </div>

                    <div class="col-span-full">
                        <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Additional Information</label>
                        <div class="mt-2">
                            <textarea id="notes" name="notes" rows="3"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">{{ old('notes', $weightLog->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                <a href="{{ route('weight-logs.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                <button type="submit" class="rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Update Weighing</button>
            </div>
        </form>
    </div>
</x-app-layout>
