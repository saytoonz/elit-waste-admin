<x-app-layout>
    @section('header')
        Edit Service Plan
    @endsection

    <div class="max-w-xl mx-auto">
        <form method="POST" action="{{ route('service_plans.update', $plan) }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
            @csrf
            @method('PUT')
            
            <div class="px-4 py-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    
                    <div class="col-span-full">
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Plan Name</label>
                        <div class="mt-2">
                            <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Price (GHS)</label>
                        <div class="mt-2">
                            <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount', $plan->amount) }}" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="billing_cycle" class="block text-sm font-medium leading-6 text-gray-900">Billing Cycle</label>
                        <div class="mt-2">
                            <select id="billing_cycle" name="billing_cycle" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:max-w-xs sm:text-sm sm:leading-6">
                                <option value="Monthly" {{ $plan->billing_cycle == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="Quarterly" {{ $plan->billing_cycle == 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="Yearly" {{ $plan->billing_cycle == 'Yearly' ? 'selected' : '' }}>Yearly</option>
                                <option value="Weekly" {{ $plan->billing_cycle == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                            </select>
                             <x-input-error :messages="$errors->get('billing_cycle')" class="mt-2" />
                        </div>
                    </div>

                    <div class="col-span-full">
                        <label for="description" class="block text-sm font-medium leading-6 text-gray-900">Description</label>
                        <div class="mt-2">
                            <textarea id="description" name="description" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">{{ old('description', $plan->description) }}</textarea>
                             <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                    </div>

                     <div class="col-span-full">
                        <div class="relative flex gap-x-3">
                            <div class="flex h-6 items-center">
                                <!-- Hidden input to ensure 0 is sent when unchecked -->
                                <input type="hidden" name="is_active" value="0">
                                <input id="is_active" name="is_active" value="1" type="checkbox" {{ $plan->is_active ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                            </div>
                            <div class="text-sm leading-6">
                                <label for="is_active" class="font-medium text-gray-900">Active</label>
                                <p class="text-gray-500">Enable or disable this plan.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="flex items-center justify-between border-t border-gray-900/10 px-4 py-4 sm:px-8">
                 <button type="button" onclick="confirm('Are you sure you want to delete this plan?') || event.preventDefault(); document.getElementById('delete-plan-form').submit();" class="text-sm font-semibold leading-6 text-red-600 hover:text-red-500">Delete Plan</button>
                
                <div class="flex items-center gap-x-6">
                    <a href="{{ route('service_plans.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save Changes</button>
                </div>
            </div>
        </form>

        <form id="delete-plan-form" action="{{ route('service_plans.destroy', $plan) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-app-layout>
