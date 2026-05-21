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
];
