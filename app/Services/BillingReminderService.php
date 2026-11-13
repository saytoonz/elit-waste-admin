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

    /**
     * One consolidated reminder for all of a customer's unpaid invoices: total
     * balance, earliest due date. Null if there's no phone or no invoices.
     *
     * @param \Illuminate\Support\Collection<int, Invoice> $invoices same customer
     */
    public function messageForCustomer($invoices): ?string
    {
        if ($invoices->isEmpty()) return null;
        if ($invoices->count() === 1) return $this->messageFor($invoices->first());

        $customer = $invoices->first()->customer;
        if (! $customer || ! $customer->phone) {
            return null;
        }

        $sorted   = $invoices->sortBy(fn($i) => $i->due_date ? Carbon::parse($i->due_date)->timestamp : PHP_INT_MAX)->values();
        $earliest = $sorted->first();
        $due      = $earliest->due_date ? Carbon::parse($earliest->due_date) : null;
        $cycle    = $earliest->subscription->billing_cycle ?? 'Monthly';
        $others   = $sorted->count() - 1;

        $replacements = [
            '{company}'  => $this->companyName(),
            '{name}'     => $this->firstName($customer->name),
            '{amount}'   => 'GHS ' . number_format((float) $invoices->sum('balance_due'), 2),
            '{invoice}'  => $earliest->invoice_number . " + {$others} more",
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
