<?php

namespace App\Support;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Runtime settings for customer billing reminders. Stored in the `settings`
 * table; writes bypass the encrypted cast (raw query + Crypt) to avoid a
 * DecryptException when overwriting stale ciphertext — same approach as
 * App\Support\PlatformConfig.
 */
class BillingConfig
{
    public const KEY_ENABLED     = 'billing_reminders_enabled';
    public const KEY_SINCE       = 'billing_reminders_since';
    public const KEY_LAST_RUN    = 'billing_reminder_last_run';
    public const KEY_MAX_DAYS    = 'billing_reminder_max_days';
    public const KEY_TPL_MONTHLY = 'sms_reminder_monthly';
    public const KEY_TPL_WEEKLY  = 'sms_reminder_weekly';

    /** Master switch for daily customer payment reminders. Default-on. */
    public static function remindersEnabled(): bool
    {
        $raw = Setting::safeValue(self::KEY_ENABLED);
        if ($raw === null) return true;
        return in_array(strtolower((string) $raw), ['1', 'true', 'yes', 'on'], true);
    }

    public static function setRemindersEnabled(bool $enabled): void
    {
        self::upsert(self::KEY_ENABLED, $enabled ? '1' : '0');
    }

    /**
     * Cutoff timestamp: only invoices created at/after this are ever reminded, so
     * the pre-existing backlog is never texted. Null when never initialised.
     */
    public static function since(): ?Carbon
    {
        $v = Setting::safeValue(self::KEY_SINCE);
        if (! is_string($v) || trim($v) === '') return null;
        try {
            return Carbon::parse($v);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function setSince($when): void
    {
        $when = $when instanceof Carbon ? $when : Carbon::parse($when);
        self::upsert(self::KEY_SINCE, $when->toDateTimeString());
    }

    /** Return the cutoff, initialising it to now() the first time (reminds nothing that run). */
    public static function ensureSince(): Carbon
    {
        $since = self::since();
        if ($since === null) {
            $since = Carbon::now();
            self::setSince($since);
        }
        return $since;
    }

    /** Y-m-d of the last reminder run — guards against duplicate sends in a day. */
    public static function lastRun(): ?string
    {
        $v = Setting::safeValue(self::KEY_LAST_RUN);
        return is_string($v) && trim($v) !== '' ? trim($v) : null;
    }

    public static function setLastRun(string $date): void
    {
        self::upsert(self::KEY_LAST_RUN, $date);
    }

    /** Default reminder window when no setting is stored. */
    public const DEFAULT_MAX_DAYS = 14;

    /** While overdue, remind every this many days. */
    public const OVERDUE_EVERY_DAYS = 3;

    /** Stop reminding an invoice this many days after it was billed. 0 = unlimited. */
    public static function maxDays(): int
    {
        $v = Setting::safeValue(self::KEY_MAX_DAYS);
        return $v === null ? self::DEFAULT_MAX_DAYS : max(0, (int) $v);
    }

    /**
     * Whether an invoice is on a reminder day: 1 day after billing, on its due
     * date, then every OVERDUE_EVERY_DAYS days while overdue (the maxDays
     * window is applied separately). Without a due date: day 1, then every
     * OVERDUE_EVERY_DAYS days.
     */
    public static function isReminderDay(Carbon $billedOn, ?Carbon $dueDate, Carbon $today): bool
    {
        $age = (int) $billedOn->copy()->startOfDay()->diffInDays($today->copy()->startOfDay(), false);
        if ($age < 1) return false;
        if ($age === 1) return true;

        if ($dueDate === null) {
            return $age % self::OVERDUE_EVERY_DAYS === 0;
        }

        $untilDue = (int) $today->copy()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false);
        if ($untilDue === 0) return true;
        if ($untilDue > 0) return false;

        return abs($untilDue) % self::OVERDUE_EVERY_DAYS === 0;
    }

    /** Optional per-cycle SMS template; null falls back to the service default. */
    public static function template(string $cycle): ?string
    {
        $key = strtolower($cycle) === 'weekly' ? self::KEY_TPL_WEEKLY : self::KEY_TPL_MONTHLY;
        $v = Setting::safeValue($key);
        return is_string($v) && trim($v) !== '' ? $v : null;
    }

    protected static function upsert(string $key, string $value): void
    {
        $encrypted = Crypt::encryptString($value);
        $now = now();
        if (DB::table('settings')->where('key', $key)->exists()) {
            DB::table('settings')->where('key', $key)->update(['value' => $encrypted, 'updated_at' => $now]);
        } else {
            DB::table('settings')->insert(['key' => $key, 'value' => $encrypted, 'created_at' => $now, 'updated_at' => $now]);
        }
    }
}
