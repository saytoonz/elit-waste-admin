<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = \App\Models\Setting::all()->pluck('value', 'key');

        return view('settings.index', [
            'paystackPublic' => $settings['paystack_public_key'] ?? '',
            'paystackSecret' => $settings['paystack_secret_key'] ?? '',
            'companyName' => $settings['company_name'] ?? 'Elite Waste Management',
            'companyPhone' => $settings['company_phone'] ?? '',
            'companyLogo' => $settings['company_logo'] ?? null,
            'currencySymbol' => $settings['currency_symbol'] ?? 'GHS',
            'vatRate' => $settings['vat_rate'] ?? '0',
            'invoiceDueDays' => $settings['invoice_due_days'] ?? '7',
            'smsApiKey' => $settings['sms_api_key'] ?? '',
            'smsSenderId' => $settings['sms_sender_id'] ?? 'EliteWaste',
            'smsBaseUrl' => $settings['sms_base_url'] ?? 'https://apiv2.mycsms.com',
            'smsWelcomeTemplate' => $settings['sms_welcome_template'] ?? 'Welcome to Elite Waste, {firstname}! Your account has been created.',
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
            // Use 'public' disk to ensure it goes to storage/app/public
            $path = $request->file('company_logo')->store('logos', 'public');
            
            // Path returned is 'logos/filename.jpg', preprend 'storage/' for asset()
            $publicPath = 'storage/' . $path;
            
            Setting::updateOrCreate(
                ['key' => 'company_logo'],
                ['value' => $publicPath]
            );
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
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $request->input($key)]
                );
            }
        }

        \App\Services\AuditService::log('Updated Settings', 'System settings were modified.');

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
