<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                // Determine config keys we care about
                $keys = ['paystack_public_key', 'paystack_secret_key', 'sms_api_key', 'sms_sender_id', 'company_name', 'company_logo', 'sms_base_url'];
                
                // Fetch valid settings
                $settings = \App\Models\Setting::whereIn('key', $keys)->get()->pluck('value', 'key');

                // Config Overrides
                if (!empty($settings['paystack_public_key'])) config(['services.paystack.publicKey' => $settings['paystack_public_key']]);
                if (!empty($settings['paystack_secret_key'])) config(['services.paystack.secret' => $settings['paystack_secret_key']]);
                if (!empty($settings['sms_api_key'])) config(['services.mycsms.api_key' => $settings['sms_api_key']]);
                if (!empty($settings['sms_sender_id'])) config(['services.mycsms.sender_id' => $settings['sms_sender_id']]);
                if (!empty($settings['sms_base_url'])) config(['services.mycsms.url' => $settings['sms_base_url']]);

                // Share Branding Globally
                $companyName = $settings['company_name'] ?? config('app.name');
                $companyLogo = $settings['company_logo'] ?? null;

                \Illuminate\Support\Facades\View::share('globalCompanyName', $companyName);
                \Illuminate\Support\Facades\View::share('globalCompanyLogo', $companyLogo);
                config(['app.name' => $companyName]);
            }
        } catch (\Exception $e) {
            // Failsafe if DB not ready
        }
    }
}
