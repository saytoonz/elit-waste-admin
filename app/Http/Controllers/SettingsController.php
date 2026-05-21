<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        // Decrypt-safe read — rows that fail to decrypt (e.g. APP_KEY mismatch) come back as null
        $settings = Setting::safeAll();

        // Flag any rows that exist but failed to decrypt so we can warn the user
        $undecryptableKeys = [];
        foreach (Setting::all(['key']) as $row) {
            if (!array_key_exists($row->key, $settings) || $settings[$row->key] === null) {
                $undecryptableKeys[] = $row->key;
            }
        }

        return view('settings.index', [
            'paystackPublic'      => $settings['paystack_public_key'] ?? '',
            'paystackSecret'      => $settings['paystack_secret_key'] ?? '',
            'companyName'         => $settings['company_name'] ?? 'Elite Waste Management',
            'companyPhone'        => $settings['company_phone'] ?? '',
            'companyLogo'         => $settings['company_logo'] ?? null,
            'currencySymbol'      => $settings['currency_symbol'] ?? 'GHS',
            'vatRate'             => $settings['vat_rate'] ?? '0',
            'invoiceDueDays'      => $settings['invoice_due_days'] ?? '7',
            'smsApiKey'           => $settings['sms_api_key'] ?? '',
            'smsSenderId'         => $settings['sms_sender_id'] ?? 'EliteWaste',
            'smsBaseUrl'          => $settings['sms_base_url'] ?? 'https://app.mycsms.com/api/v3/sms/send',
            'smsWelcomeTemplate'  => $settings['sms_welcome_template'] ?? 'Welcome to Elite Waste, {firstname}! Your account has been created.',
            'undecryptableKeys'   => $undecryptableKeys,
            'providerPaystackConfigured' => !empty(config('services.paystack.provider_secret')),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'paystack_public_key' => 'nullable|string',
            'paystack_secret_key' => 'nullable|string',
            'company_name' => 'nullable|string',
            'company_phone' => 'nullable|string',
            'currency_symbol' => 'nullable|string',
            'vat_rate' => 'nullable|numeric|min:0',
            'invoice_due_days' => 'nullable|integer|min:1',
            'sms_api_key' => 'nullable|string',
            'sms_sender_id' => 'nullable|string|max:11',
            'sms_base_url' => 'nullable|url',
            'sms_welcome_template' => 'nullable|string',
        ]);

        // Handle Logo Upload
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('logos', 'public');
            $publicPath = 'storage/' . $path;
            $this->upsertSetting('company_logo', $publicPath);
        }

        $keys = [
            'paystack_public_key',
            'paystack_secret_key',
            'company_name',
            'company_phone',
            'currency_symbol',
            'vat_rate',
            'invoice_due_days',
            'sms_api_key',
            'sms_sender_id',
            'sms_base_url',
            'sms_welcome_template',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                $this->upsertSetting($key, $request->input($key));
            }
        }

        \App\Services\AuditService::log('Updated Settings', 'System settings were modified.');

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }

    /**
     * Upsert a setting without going through Eloquent's encrypted-cast hydration.
     * This sidesteps DecryptException on existing ciphertext from a stale APP_KEY —
     * the old value is simply overwritten with the freshly-encrypted new one.
     */
    private function upsertSetting(string $key, $value): void
    {
        $encrypted = Crypt::encryptString((string) $value);
        $now = now();

        if (DB::table('settings')->where('key', $key)->exists()) {
            DB::table('settings')->where('key', $key)->update([
                'value'      => $encrypted,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('settings')->insert([
                'key'        => $key,
                'value'      => $encrypted,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
