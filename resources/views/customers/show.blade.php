<x-app-layout>
    @section('header')
        Customer Profile
    @endsection

    <div class="mb-6 flex flex-col gap-y-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">{{ $customer->name }}</h2>
            <p class="text-sm text-gray-500">Member since {{ $customer->created_at->format('M d, Y') }}</p>
        </div>
        <div class="flex gap-x-3">
            <a href="{{ route('customers.edit', $customer) }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Edit Profile</a>
            <a href="{{ route('invoices.create', ['customer_id' => $customer->id]) }}" class="rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">New Invoice</a>
        </div>
    </div>

    <!-- Dynamic Stats -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-8">
        <div class="bg-white overflow-hidden rounded-lg shadow-sm border border-gray-100 p-6">
             <dt class="truncate text-sm font-medium text-gray-500">Current Balance</dt>
             <dd class="mt-2 text-3xl font-semibold tracking-tight {{ $customer->balance > 0 ? 'text-red-600' : 'text-gray-900' }}">
                 GHS {{ number_format($customer->balance, 2) }}
             </dd>
        </div>
         <div class="bg-white overflow-hidden rounded-lg shadow-sm border border-gray-100 p-6 relative group">
             <dt class="truncate text-sm font-medium text-gray-500">Service Plan</dt>
             <dd class="mt-2 text-xl font-semibold tracking-tight text-gray-900">
                @if($customer->subscription)
                    {{ $customer->subscription->billing_cycle }} (GHS {{ number_format($customer->subscription->amount, 2) }})
                    <span class="block text-xs text-gray-500 font-normal mt-1">Next Bill: {{ $customer->subscription->next_billing_date->format('M d, Y') }}</span>
                @else
                    No Active Plan
                @endif
             </dd>
             <!-- Action to Add/Edit Plan -->
             <div class="absolute top-4 right-4">
                 <button onclick="document.getElementById('subscription-modal').showModal()" class="text-xs text-primary hover:underline">Manage</button>
             </div>
        </div>
         <div class="bg-white overflow-hidden rounded-lg shadow-sm border border-gray-100 p-6">
             <dt class="truncate text-sm font-medium text-gray-500">Status</dt>
             <dd class="mt-2">
                 @if($customer->is_active)
                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                @else
                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Inactive</span>
                @endif
             </dd>
        </div>
    </div>

    <!-- ... (Contact Info Column remains same, skipping for brevity in replacement if possible, but I need to match structure) ... -->
    
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left Column: Details -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl lg:col-span-1 h-fit">
            <div class="px-4 py-6 sm:px-6">
                <h3 class="text-base font-semibold leading-7 text-gray-900">Contact Information</h3>
            </div>
            <div class="border-t border-gray-100 px-4 py-6 sm:px-6">
                <dl class="divide-y divide-gray-100">
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $customer->phone }}</dd>
                    </div>
                    @if($customer->secondary_phone)
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Alt Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $customer->secondary_phone }}</dd>
                    </div>
                    @endif
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $customer->address }}</dd>
                    </div>
                     <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Landmark</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $customer->landmark }}</dd>
                    </div>
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Zone</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $customer->zone->name ?? 'N/A' }}</dd>
                    </div>
                     <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $customer->type }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Right Column: Tabs (Invoices) -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl lg:col-span-2 min-h-[400px]">
             <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <a href="#" class="border-primary text-primary whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium" aria-current="page">Invoices</a>
                    <!-- Future: Payments Tab -->
                </nav>
            </div>
            
            <div class="p-0">
                @if($customer->invoices->count() > 0)
                <div class="overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Invoice #</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Balance</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($customer->invoices as $invoice)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $invoice->invoice_number }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $invoice->created_at->format('M d, Y') }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">GHS {{ number_format($invoice->amount, 2) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium {{ $invoice->balance_due > 0 ? 'text-red-600' : 'text-green-600' }}">GHS {{ number_format($invoice->balance_due, 2) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                         <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $invoice->status == 'Paid' ? 'bg-green-50 text-green-700 ring-green-600/20' : ($invoice->status == 'Overdue' ? 'bg-red-50 text-red-700 ring-red-600/20' : 'bg-yellow-50 text-yellow-800 ring-yellow-600/20') }}">
                                            {{ $invoice->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No invoices found</h3>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Subscription Modal -->
    <dialog id="subscription-modal" class="modal p-0 rounded-lg shadow-xl backdrop:bg-gray-900/50">
        <div class="p-6 w-full max-w-lg bg-white">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $customer->subscription ? 'Edit Subscription' : 'Create Subscription' }}</h3>
            <form method="POST" action="{{ $customer->subscription ? route('subscriptions.update', $customer->subscription) : route('subscriptions.store') }}">
                @csrf
                @if($customer->subscription)
                    @method('PUT')
                @else
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                @endif

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Billing Cycle</label>
                        <select name="billing_cycle" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="Weekly" {{ ($customer->subscription->billing_cycle ?? '') == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="Monthly" {{ ($customer->subscription->billing_cycle ?? '') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="Quarterly" {{ ($customer->subscription->billing_cycle ?? '') == 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount (GHS)</label>
                        <input type="number" step="0.01" name="amount" value="{{ $customer->subscription->amount ?? '' }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    </div>

                    @if(!$customer->subscription)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Due Date Rule (Days after billing)</label>
                        <input type="number" name="due_date_offset_days" value="{{ $customer->subscription->due_date_offset_days ?? 7 }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    </div>
                    
                    @if($customer->subscription)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                         <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            <option value="Active" {{ ($customer->subscription->status ?? '') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ ($customer->subscription->status ?? '') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('subscription-modal').close()" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 border rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-primary rounded-md hover:bg-secondary">Save Plan</button>
                </div>
            </form>
        </div>
    </dialog>
</x-app-layout>
