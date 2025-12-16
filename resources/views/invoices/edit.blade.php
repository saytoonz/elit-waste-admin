<x-app-layout>
    @section('header')
        Edit Invoice: {{ $invoice->invoice_number }}
    @endsection

    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('invoices.update', $invoice) }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
            @csrf
            @method('PUT')
            
            <div class="px-4 py-6 sm:p-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    
                    <div class="col-span-full">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Customer</label>
                        <div class="mt-2">
                            <input type="text" disabled value="{{ $invoice->customer->name }}" class="block w-full rounded-md border-0 py-1.5 text-gray-500 bg-gray-50 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="amount" class="block text-sm font-medium leading-6 text-gray-900">Amount (GHS)</label>
                        <div class="mt-2">
                            <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount', $invoice->amount) }}" required
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>
                    </div>

                     <div class="sm:col-span-3">
                        <label for="status" class="block text-sm font-medium leading-6 text-gray-900">Status</label>
                        <div class="mt-2">
                            <select name="status" id="status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                                <option value="Pending" {{ $invoice->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Paid" {{ $invoice->status == 'Paid' ? 'selected' : '' }}>Paid</option>
                                <option value="Overdue" {{ $invoice->status == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                                <option value="Cancelled" {{ $invoice->status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="due_date" class="block text-sm font-medium leading-6 text-gray-900">Due Date</label>
                        <div class="mt-2">
                            <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                             <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                        </div>
                    </div>

                    <div class="col-span-full">
                        <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Notes / Description</label>
                        <div class="mt-2">
                            <textarea id="notes" name="notes" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">{{ old('notes', $invoice->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                <a href="{{ route('invoices.index') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                <button type="submit" class="rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Update Invoice</button>
            </div>
        </form>

        <div class="mt-6 flex justify-end">
             <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-800">Delete Invoice</button>
            </form>
        </div>
    </div>
</x-app-layout>
