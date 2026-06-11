<x-app-layout>
    @section('header') Platform Settings @endsection

    @if(session('success'))<div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>@endif

    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Payments toggle -->
        <form method="POST" action="{{ route('platform.settings.update') }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            @csrf

            <div class="px-6 py-5 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Payment Availability</h2>
                <p class="text-sm text-gray-600 mt-1">Pause all customer payment actions (Pay Now, Pay All, Pre-pay, Email purchase). Subscriptions keep generating invoices on schedule — only the payment step is blocked.</p>
            </div>

            <div class="px-6 py-6 space-y-5">
                <div class="flex items-start gap-3">
                    <input type="hidden" name="payments_enabled" value="0">
                    <input type="checkbox" name="payments_enabled" id="payments_enabled" value="1" @checked($paymentsEnabled)
                        class="mt-1 h-5 w-5 rounded border-gray-300 text-primary focus:ring-primary">
                    <div>
                        <label for="payments_enabled" class="text-sm font-medium text-gray-900">Allow customers to make payments</label>
                        <p class="text-xs text-gray-500 mt-0.5">Currently
                            @if($paymentsEnabled)
                                <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">Enabled</span>
                            @else
                                <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">Paused</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900">Maintenance Message (optional)</label>
                    <textarea name="maintenance_message" rows="3" placeholder="Shown to customers when payments are paused. E.g. 'Bank reconciliation in progress — payments resume tomorrow at 9am.'"
                        class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">{{ $maintenanceMessage }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Leave blank to use the default message.</p>
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <label for="paystack_fee_percent" class="block text-sm font-medium text-gray-900">Paystack Processing Fee (%)</label>
                    <div class="mt-2 flex items-center gap-2">
                        <input type="number" name="paystack_fee_percent" id="paystack_fee_percent" step="0.01" min="0" max="15"
                               value="{{ old('paystack_fee_percent', $paystackFeePercent) }}"
                               class="block w-32 rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm">
                        <span class="text-sm text-gray-500">%</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Added on top of <strong>every</strong> Paystack charge — platform invoices and waste-collection invoices alike — so the payer absorbs the gateway fee.
                        Set to 0 to absorb fees yourself. Applies immediately to new payment initiations; invoices keep their face value.
                    </p>
                    @error('paystack_fee_percent')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-x-4 border-t border-gray-900/10 px-6 py-4">
                <button type="submit" class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-secondary">Save Settings</button>
            </div>
        </form>

        <!-- Read-only platform config -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-3">Environment Configuration</h3>
            <p class="text-sm text-gray-600 mb-4">These values come from the server's <span class="font-mono">.env</span> file and require a deploy + <span class="font-mono">php artisan config:cache</span> to change.</p>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Email Domain</dt>
                    <dd class="font-mono text-gray-900">{{ $emailDomain ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Provider Charge Currency</dt>
                    <dd class="font-mono text-gray-900">{{ $providerChargeCurrency ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">USD → GHS Rate</dt>
                    <dd class="font-mono text-gray-900">{{ $usdToGhsRate ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Full-app maintenance hint -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 text-sm text-gray-700">
            <h3 class="font-semibold text-gray-900 mb-1">Full-app Maintenance Mode</h3>
            <p>The toggle above only pauses <em>payments</em>. To take the entire application offline (e.g. for a migration), SSH in and run:</p>
            <pre class="mt-2 bg-white rounded-md p-3 font-mono text-xs border border-gray-200">php artisan down --secret="your-bypass-token"</pre>
            <p class="mt-2 text-xs text-gray-500">Bring it back with <span class="font-mono">php artisan up</span>. The secret token lets you keep accessing the app while everyone else sees a 503 page.</p>
        </div>
    </div>
</x-app-layout>
