<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Support\PlatformConfig;
use Illuminate\Http\Request;

class PlatformSettingsController extends Controller
{
    public function show()
    {
        return view('platform.settings.index', [
            'paymentsEnabled'       => PlatformConfig::paymentsEnabled(),
            'maintenanceMessage'    => PlatformConfig::maintenanceMessage(),
            'providerChargeCurrency'=> config('platform.provider_charge_currency'),
            'usdToGhsRate'          => config('platform.usd_to_ghs_rate'),
            'emailDomain'           => config('platform.email_domain'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'payments_enabled'      => 'sometimes|boolean',
            'maintenance_message'   => 'nullable|string|max:500',
        ]);

        $wasEnabled = PlatformConfig::paymentsEnabled();
        $nowEnabled = (bool) $request->boolean('payments_enabled');

        PlatformConfig::setPaymentsEnabled($nowEnabled);
        PlatformConfig::setMaintenanceMessage($data['maintenance_message'] ?? null);

        if ($wasEnabled !== $nowEnabled) {
            AuditService::log(
                'Toggled Platform Payments',
                $nowEnabled ? 'Enabled' : 'Disabled — reason: ' . ($data['maintenance_message'] ?? 'n/a')
            );
        }

        return redirect()->route('platform.settings.show')
            ->with('success', 'Platform settings saved.');
    }
}
