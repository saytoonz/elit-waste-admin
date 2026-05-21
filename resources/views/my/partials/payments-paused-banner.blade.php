@php
    $paymentsEnabled = \App\Support\PlatformConfig::paymentsEnabled();
    $maintenanceMsg = \App\Support\PlatformConfig::maintenanceMessage();
@endphp
@unless($paymentsEnabled)
    <div class="mb-6 rounded-lg border border-amber-300 bg-amber-50 p-4">
        <div class="flex items-start gap-3">
            <svg class="h-5 w-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-amber-900">Payments are temporarily paused</h3>
                <p class="text-sm text-amber-800 mt-1">
                    {{ $maintenanceMsg ?: 'The service provider has paused new payments. You can still view your invoices and services. Please try again later.' }}
                </p>
            </div>
        </div>
    </div>
@endunless
