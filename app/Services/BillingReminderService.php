<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Setting;
use App\Support\BillingConfig;
use Carbon\Carbon;

/**
 * Composes the daily customer payment-reminder SMS for a single unpaid invoice.
 * Mirrors the shape of App\Services\PlatformReminderService (which reminds the
 * admin) but is customer-facing and per-invoice.
 */
class BillingReminderService
{
    /**
     * Build the reminder SMS for an invoice, or null if it can't/shouldn't be sent
     * (no customer phone). Wording adapts to days remaining vs overdue.
     */
    public function messageFor(Invoice $invoice): ?string
    {
        $customer = $invoice->customer;
        if (! $customer || ! $customer->phone) {
            return null;
        }

        $cycle = $invoice->subscription->billing_cycle ?? 'Monthly';
        $due   = $invoice->due_date ? Carbon::parse($invoice->due_date) : null;

        $replacements = [
            '{company}'  => $this->companyName(),
            '{name}'     => $this->firstName($customer->name),
            '{amount}'   => 'GHS ' . number_format((float) $invoice->balance_due, 2),
            '{invoice}'  => $invoice->invoice_number,
            '{due_date}' => $due ? $due->format('D, d M Y') : 'soon',
            '{days}'     => $this->daysPhrase($due),
        ];

        $template = BillingConfig::template($cycle) ?? $this->defaultTemplate();

        return trim(strtr($template, $replacements));
    }

    protected function defaultTemplate(): string
    {
        return '{company}: Hi {name}, your bill of {amount} (Invoice {invoice}) is {days} — due {due_date}. Please pay to keep your waste collection active.';
    }

    /** "due in N day(s)" / "due today" / "OVERDUE by N day(s)". */
    protected function daysPhrase(?Carbon $due): string
    {
        if (! $due) {
            return 'due soon';
        }

        $today = Carbon::today();
        $diff  = (int) $today->diffInDays($due->copy()->startOfDay(), false);

        if ($diff > 0)  return "due in {$diff} day" . ($diff === 1 ? '' : 's');
        if ($diff === 0) return 'due today';

        $over = abs($diff);
        return "OVERDUE by {$over} day" . ($over === 1 ? '' : 's');
    }

    protected function companyName(): string
    {
        $name = trim((string) Setting::safeValue('company_name', ''));
        return $name !== '' ? $name : 'Elite Waste';
    }

    protected function firstName(?string $full): string
    {
        $parts = preg_split('/\s+/', trim((string) $full));
        return $parts[0] !== '' ? $parts[0] : 'Customer';
    }
}
