<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Locked Email Domain
    |--------------------------------------------------------------------------
    |
    | All user accounts provisioned through the customer-facing "Add Email"
    | flow are forced onto this domain. The form locks the suffix visually
    | and the controller constructs the full address server-side.
    |
    */

    'email_domain' => env('PLATFORM_EMAIL_DOMAIN', 'elitewasteghana.com'),

    /*
    |--------------------------------------------------------------------------
    | Provider Paystack — Charge Currency
    |--------------------------------------------------------------------------
    |
    | The currency the provider's Paystack account is allowed to charge in.
    | When an invoice is in a different currency (e.g. catalog priced in USD
    | but Paystack only takes GHS), the platform converts on the fly using
    | the rate below. Invoices stay in their original currency in the DB.
    |
    */

    'provider_charge_currency' => env('PLATFORM_PROVIDER_CHARGE_CURRENCY', 'GHS'),

    /*
    |--------------------------------------------------------------------------
    | USD → GHS Conversion Rate
    |--------------------------------------------------------------------------
    |
    | Used at payment-init time when converting USD-priced platform invoices
    | into GHS for charging via the provider Paystack account. Update this as
    | your bank/FX rate changes (or wire to a live FX API later).
    |
    */

    'usd_to_ghs_rate' => (float) env('PLATFORM_USD_TO_GHS_RATE', 15.5),

    /*
    |--------------------------------------------------------------------------
    | Paystack Processing Fee (% added to every Paystack charge)
    |--------------------------------------------------------------------------
    |
    | Paystack charges the merchant ~2% per transaction. This percentage is
    | added on top of the amount at payment-init time so the payer absorbs it
    | and the merchant nets the invoice amount. This is only the DEFAULT —
    | the SuperAdmin can override it live from Platform Settings without a
    | deploy (stored in the settings table).
    |
    */

    'paystack_fee_percent' => (float) env('PLATFORM_PAYSTACK_FEE_PERCENT', 2.0),
];
