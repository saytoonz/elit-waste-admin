<?php

namespace App\Services;

use App\Models\Platform\PlatformInvoice;
use App\Models\Platform\PlatformSubscription;
use App\Models\Platform\SmsBundle;
use App\Models\Setting;
use App\Support\PlatformConfig;
use Carbon\Carbon;

/**
 * Builds the daily billing-reminder SMS sent to the customer admin: unpaid platform
 * invoices, overdue/grace warnings, upcoming renewals, and SMS bundle expiry. Kept
 * as one consolidated message to minimise SMS-credit spend.
 */
class PlatformReminderService
{
    /** How many days ahead an upcoming renewal is worth mentioning. */
    protected const RENEWAL_WINDOW_DAYS = 7;

    /** Warn about SMS bundle expiry when this few days remain. */
    protected const BUNDLE_EXPIRY_WARN_DAYS = 5;

    /** Destination phone for billing reminders: explicit override, else company phone. */
    public function reminderPhone(): ?string
    {
        $phone = PlatformConfig::reminderPhone() ?? (string) Setting::safeValue('company_phone', '');
        $phone = preg_replace('/\s+/', '', (string) $phone);
        return $phone !== '' ? $phone : null;
    }

    /** Business name used as the SMS prefix. */
    protected function companyName(): string
    {
        $name = trim((string) Setting::safeValue('company_name', ''));
        return $name !== '' ? $name : 'Elite Waste';
    }

    /**
     * Concise reminder lines relevant today. Empty array = nothing to remind about.
     */
    public function buildLines(): array
    {
        $lines = [];
        $today = Carbon::today();

        // 1) Unpaid platform invoices (Pending / Overdue / Partial)
        $unpaid = PlatformInvoice::unpaid()->get();
        if ($unpaid->isNotEmpty()) {
            $totals = $unpaid->groupBy('currency')
                ->map(fn($g, $cur) => $cur . ' ' . number_format($g->sum(fn($i) => $i->balance), 2))
                ->values()->implode(', ');

            $dueDates = $unpaid->pluck('due_date')->filter();
            $earliest = $dueDates->isNotEmpty() ? $dueDates->min() : null;
            $overdue  = $unpaid->filter(fn($i) => $i->due_date && $i->due_date->lt($today))->count();

            $line = $unpaid->count() . ' unpaid invoice(s): ' . $totals . '.';
            if ($earliest) {
                $line .= ' Earliest due ' . Carbon::parse($earliest)->format('M d') . '.';
            }
            if ($overdue > 0) {
                $line .= ' ' . $overdue . ' OVERDUE.';
            }
            $lines[] = $line;
        }

        // 2) Grace period / suspension risk
        $atRisk = PlatformSubscription::where('force_payment', true)
            ->where('status', '!=', 'Cancelled')
            ->get()
            ->filter(fn($s) => $s->shouldBlockAccess());
        if ($atRisk->isNotEmpty()) {
            $lines[] = 'Past grace period — settle now to avoid service suspension.';
        }

        // 3) Upcoming renewals (within the next week)
        $soon = PlatformSubscription::active()
            ->where('auto_renew', true)
            ->whereDate('next_billing_date', '>=', $today)
            ->whereDate('next_billing_date', '<=', $today->copy()->addDays(self::RENEWAL_WINDOW_DAYS))
            ->with('service')
            ->orderBy('next_billing_date')
            ->get();
        if ($soon->isNotEmpty()) {
            $first = $soon->first();
            $lines[] = $soon->count() . ' service(s) renew within ' . self::RENEWAL_WINDOW_DAYS
                . ' days (next: ' . ($first->service?->name ?? 'service') . ' '
                . Carbon::parse($first->next_billing_date)->format('M d') . ').';
        }

        // 4) SMS bundle expiry / low balance
        $bundle = SmsBundle::findActive();
        if ($bundle) {
            if ($bundle->days_remaining <= self::BUNDLE_EXPIRY_WARN_DAYS) {
                $lines[] = 'SMS credits expire ' . $bundle->period_end->format('M d')
                    . ' (' . number_format($bundle->remaining) . ' left).';
            } elseif ($bundle->usage_percent >= 90) {
                $lines[] = 'SMS credits low: ' . number_format($bundle->remaining) . ' left.';
            }
        }

        return $lines;
    }

    /** Full SMS message, or null if there's nothing to remind about. */
    public function compose(): ?string
    {
        $lines = $this->buildLines();
        if (empty($lines)) return null;

        return $this->companyName() . ' billing: ' . implode(' ', $lines);
    }
}
