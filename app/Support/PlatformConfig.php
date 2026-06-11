<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Read/write platform-wide runtime toggles (payments on/off, maintenance message, etc.)
 *
 * Stored in the existing `settings` table with `platform_*` prefixed keys. Writes bypass
 * Eloquent's encrypted cast (via raw query builder + Crypt::encryptString) to avoid
 * DecryptException on stale ciphertext when overwriting.
 */
class PlatformConfig
{
    public const KEY_PAYMENTS_ENABLED      = 'platform_payments_enabled';
    public const KEY_MAINTENANCE_MESSAGE   = 'platform_maintenance_message';
    public const KEY_PAYSTACK_FEE_PERCENT  = 'platform_paystack_fee_percent';

    /**
     * Whether the customer can initiate platform payments right now.
     * Defaults to true if the setting has never been written.
     */
    public static function paymentsEnabled(): bool
    {
        $raw = Setting::safeValue(self::KEY_PAYMENTS_ENABLED);
        if ($raw === null) return true; // default-on
        return in_array(strtolower((string) $raw), ['1', 'true', 'yes', 'on'], true);
    }

    public static function setPaymentsEnabled(bool $enabled): void
    {
        self::upsert(self::KEY_PAYMENTS_ENABLED, $enabled ? '1' : '0');
    }

    public static function maintenanceMessage(): ?string
    {
        $msg = Setting::safeValue(self::KEY_MAINTENANCE_MESSAGE);
        $msg = is_string($msg) ? trim($msg) : '';
        return $msg !== '' ? $msg : null;
    }

    public static function setMaintenanceMessage(?string $message): void
    {
        self::upsert(self::KEY_MAINTENANCE_MESSAGE, (string) ($message ?? ''));
    }

    /**
     * The Paystack processing fee (%) added on top of every Paystack charge so the
     * payer absorbs it. Settings-table value wins; falls back to config/env default.
     */
    public static function paystackFeePercent(): float
    {
        $raw = Setting::safeValue(self::KEY_PAYSTACK_FEE_PERCENT);
        if ($raw === null || trim((string) $raw) === '') {
            return (float) config('platform.paystack_fee_percent', 2.0);
        }
        return max(0.0, min(15.0, (float) $raw));
    }

    public static function setPaystackFeePercent(float $percent): void
    {
        self::upsert(self::KEY_PAYSTACK_FEE_PERCENT, (string) max(0.0, min(15.0, $percent)));
    }

    /**
     * Raw upsert that bypasses the encrypted cast collision when overwriting stale ciphertext.
     */
    protected static function upsert(string $key, string $value): void
    {
        $encrypted = Crypt::encryptString($value);
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
