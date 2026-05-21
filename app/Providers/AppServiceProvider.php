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
                // Decrypt-safe read — bad ciphertext rows come back as null instead of throwing
                $settings = \App\Models\Setting::safeAll();

                // Config Overrides (only when present and non-empty)
                if (!empty($settings['paystack_public_key'])) config(['services.paystack.publicKey' => $settings['paystack_public_key']]);
                if (!empty($settings['paystack_secret_key'])) config(['services.paystack.secret' => $settings['paystack_secret_key']]);
                if (!empty($settings['sms_api_key']))        config(['services.mycsms.api_key' => $settings['sms_api_key']]);
                if (!empty($settings['sms_sender_id']))      config(['services.mycsms.sender_id' => $settings['sms_sender_id']]);

                // SMS base URL: reject obviously-deprecated v2 endpoints so a stale Settings row
                // can't trap the system on a dead API. Customer keeps the env/v3 default in that case.
                if (!empty($settings['sms_base_url']) && !$this->isDeprecatedSmsUrl($settings['sms_base_url'])) {
                    config(['services.mycsms.url' => $settings['sms_base_url']]);
                }

                // Share Branding Globally
                $companyName = $settings['company_name'] ?? config('app.name');
                $companyLogo = $settings['company_logo'] ?? null;

                \Illuminate\Support\Facades\View::share('globalCompanyName', $companyName);
                \Illuminate\Support\Facades\View::share('globalCompanyLogo', $companyLogo);
                config(['app.name' => $companyName]);
            }
        } catch (\Throwable $e) {
            // Failsafe if DB not ready (early boot, fresh install, migrations not run, etc.)
        }
    }

    /**
     * Treat the legacy v2 MyCSMS hostname as deprecated so it never overrides the v3 default.
     */
    protected function isDeprecatedSmsUrl(?string $url): bool
    {
        if (!$url) return false;
        return str_contains(strtolower($url), 'apiv2.mycsms');
    }
}
