<x-app-layout>
    @section('header')
        System Settings
    @endsection

    <div class="max-w-4xl mx-auto bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <form method="POST" action="{{ route('settings.update') }}" class="p-6 md:p-8" enctype="multipart/form-data">
            @csrf
            
            <div class="space-y-12">
                <!-- General Settings -->
                <div class="border-b border-gray-900/10 pb-12">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">General Information</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-600">Basic platform details visible on invoices and reports.</p>

                    <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="col-span-full">
                            <label for="company_logo" class="block text-sm font-medium leading-6 text-gray-900">Company Logo</label>
                            <div class="mt-2 flex items-center gap-x-3">
                                @if(!empty($companyLogo))
                                    <img class="h-12 w-12 rounded-full object-cover bg-gray-50 border border-gray-200" src="{{ asset($companyLogo) }}" alt="Current Logo">
                                @else
                                    <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 border border-gray-200">
                                        <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    </div>
                                @endif
                                <input type="file" name="company_logo" id="company_logo" accept="image/*"
                                    class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Will be used as the app icon (favicon) and header logo.</p>
                        </div>

                        <div class="sm:col-span-4">
                            <label for="company_name" class="block text-sm font-medium leading-6 text-gray-900">Company Name</label>
                            <div class="mt-2">
                                <input type="text" name="company_name" id="company_name" value="{{ $companyName }}" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="sm:col-span-4">
                            <label for="company_phone" class="block text-sm font-medium leading-6 text-gray-900">Support Phone</label>
                            <div class="mt-2">
                                <input type="text" name="company_phone" id="company_phone" value="{{ $companyPhone }}" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Configuration -->
                <div class="border-b border-gray-900/10 pb-12">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">Invoicing & Billing</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-600">Configure how invoices are generated and currency details.</p>

                    <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label for="currency_symbol" class="block text-sm font-medium leading-6 text-gray-900">Currency Symbol</label>
                            <div class="mt-2">
                                <input type="text" name="currency_symbol" id="currency_symbol" value="{{ $currencySymbol }}" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="vat_rate" class="block text-sm font-medium leading-6 text-gray-900">VAT Rate (%)</label>
                            <div class="mt-2">
                                <input type="number" step="0.01" name="vat_rate" id="vat_rate" value="{{ $vatRate }}" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            </div>
                        </div>

                         <div class="sm:col-span-2">
                            <label for="invoice_due_days" class="block text-sm font-medium leading-6 text-gray-900">Default Due Days</label>
                            <div class="mt-2">
                                <input type="number" name="invoice_due_days" id="invoice_due_days" value="{{ $invoiceDueDays }}" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Configuration -->
                <div class="border-b border-gray-900/10 pb-12">
                     <div class="flex items-center gap-2">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">Payment Gateway — Your Paystack Account</h2>
                         <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Active</span>
                     </div>
                    <p class="mt-1 text-sm leading-6 text-gray-600">These keys are used when <strong>your customers</strong> pay you for waste-collection invoices. Payouts go to your Paystack account.</p>
                    @if(!empty($undecryptableKeys ?? []))
                        <div class="mt-3 rounded-md bg-yellow-50 border border-yellow-200 p-3 text-sm text-yellow-900">
                            <strong>{{ count($undecryptableKeys) }} setting(s) could not be decrypted</strong> — likely an APP_KEY change. Re-enter the values below to fix:
                            <span class="font-mono">{{ implode(', ', $undecryptableKeys) }}</span>
                        </div>
                    @endif
                    @if(isset($providerPaystackConfigured))
                        <div class="mt-3 rounded-md {{ $providerPaystackConfigured ? 'bg-blue-50 border-blue-200 text-blue-900' : 'bg-amber-50 border-amber-200 text-amber-900' }} border p-3 text-xs">
                            <strong>Provider (platform) Paystack:</strong>
                            {{ $providerPaystackConfigured ? 'configured via .env (separate from your account above).' : 'not yet configured. Set PAYSTACK_PROVIDER_SECRET_KEY and PAYSTACK_PROVIDER_PUBLIC_KEY in .env on the server to enable hosting/email/domain billing.' }}
                        </div>
                    @endif

                    <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-4">
                            <label for="paystack_public_key" class="block text-sm font-medium leading-6 text-gray-900">Paystack Public Key</label>
                            <div class="mt-2">
                                <input type="text" name="paystack_public_key" id="paystack_public_key" value="{{ $paystackPublic }}" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="sm:col-span-4">
                            <label for="paystack_secret_key" class="block text-sm font-medium leading-6 text-gray-900">Paystack Secret Key</label>
                            <div class="mt-2 relative">
                                <input type="password" name="paystack_secret_key" id="paystack_secret_key" value="{{ $paystackSecret }}" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            </div>
                             <p class="mt-2 text-xs text-gray-500">Values are encrypted securely. Leave empty to keep current value.</p>
                        </div>
                    </div>
                </div>

                <!-- SMS Configuration -->
                <div class="border-b border-gray-900/10 pb-12">
                     <div class="flex items-center gap-2">
                        <h2 class="text-base font-semibold leading-7 text-gray-900">SMS Notifications (MyCSMS)</h2>
                     </div>
                    <p class="mt-1 text-sm leading-6 text-gray-600">Configure SMS gateway credentials to send alerts.</p>

                    <div class="mt-8 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                        <div class="sm:col-span-4">
                            <label for="sms_api_key" class="block text-sm font-medium leading-6 text-gray-900">API Key</label>
                            <div class="mt-2 relative">
                                <input type="password" name="sms_api_key" id="sms_api_key" value="{{ $smsApiKey }}" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="sm:col-span-4">
                            <label for="sms_base_url" class="block text-sm font-medium leading-6 text-gray-900">Base URL</label>
                            <div class="mt-2">
                                <input type="url" name="sms_base_url" id="sms_base_url" value="{{ $smsBaseUrl }}" placeholder="https://apiv2.mycsms.com"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="sms_sender_id" class="block text-sm font-medium leading-6 text-gray-900">Sender ID</label>
                            <div class="mt-2">
                                <input type="text" name="sms_sender_id" id="sms_sender_id" value="{{ $smsSenderId }}" maxlength="11"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="col-span-full">
                            <label for="sms_welcome_template" class="block text-sm font-medium leading-6 text-gray-900">Welcome Message Template</label>
                            <div class="mt-2">
                                <textarea name="sms_welcome_template" id="sms_welcome_template" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6">{{ $smsWelcomeTemplate }}</textarea>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-gray-600">Available variables: <code>{firstname}</code>, <code>{lastname}</code>, <code>{service_type}</code>.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-x-6">
                <a href="{{ route('dashboard') }}" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                <button type="submit" class="rounded-md bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Save Changes</button>
            </div>
        </form>
    </div>
</x-app-layout>
