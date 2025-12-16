<x-app-layout>
    @section('header')
        Create New Invoice
    @endsection

    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('invoices.store') }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
            @csrf
            
            <div class="px-4 py-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    
                    <div class="col-span-full">
                        <label for="customer_id" class="block text-sm font-medium leading-6 text-gray-900">Customer</label>
                        <div class="mt-2">
                             <select id="customer_id" name="customer_id" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6" required>
                                <option value="">Select a customer</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ (old('customer_id', request('customer_id')) == $c->id) ? 'selected' : '' }}>{{ $c->name }} ({{ $c->phone }})</option>
                                @endforeach
                            </select>
                             <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Amount (GHS)</label>
                        <div class="mt-2">
                            <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}" required
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="due_date" class="block text-sm font-medium leading-6 text-gray-900">Due Date</label>
                        <div class="mt-2">
                            <input type="date" name="due_date" id="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+7 days'))) }}" required
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                             <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                        </div>
                    </div>

                    <div class="col-span-full">
                        <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Notes / Description</label>
                        <div class="mt-2">
                            <textarea id="notes" name="notes" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                <a href="{{ route('invoices.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                <button type="submit" class="rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Create Invoice</button>
            </div>
        </form>
    </div>
</x-app-layout>
