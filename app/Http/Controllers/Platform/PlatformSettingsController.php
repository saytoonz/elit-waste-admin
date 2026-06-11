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
            'paystackFeePercent'    => PlatformConfig::paystackFeePercent(),
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
            'paystack_fee_percent'  => 'required|numeric|min:0|max:15',
        ]);

        $wasEnabled = PlatformConfig::paymentsEnabled();
        $nowEnabled = (bool) $request->boolean('payments_enabled');
        $oldFee = PlatformConfig::paystackFeePercent();
        $newFee = (float) $data['paystack_fee_percent'];

        PlatformConfig::setPaymentsEnabled($nowEnabled);
        PlatformConfig::setMaintenanceMessage($data['maintenance_message'] ?? null);
        PlatformConfig::setPaystackFeePercent($newFee);

        if ($wasEnabled !== $nowEnabled) {
            AuditService::log(
                'Toggled Platform Payments',
                $nowEnabled ? 'Enabled' : 'Disabled — reason: ' . ($data['maintenance_message'] ?? 'n/a')
            );
        }
        if (abs($oldFee - $newFee) > 0.0001) {
            AuditService::log('Changed Paystack Fee', "{$oldFee}% → {$newFee}%");
        }

        return redirect()->route('platform.settings.show')
            ->with('success', 'Platform settings saved.');
    }
}
