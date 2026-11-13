<?php

namespace App\Console\Commands;

use App\Jobs\SendSmsJob;
use App\Models\Invoice;
use App\Services\AuditService;
use App\Services\BillingReminderService;
use App\Support\BillingConfig;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * SMS reminder to customers with an unpaid subscription bill — one message per
 * customer, sent on reminder days only (day after billing, due date, then every
 * few days while overdue; see BillingConfig::isReminderDay) and only within the
 * maxDays window. Uses the reminder SMS profile, so it never spends the credit
 * reserve kept for payment confirmations and invoice notices.
 *
 * Scope guard: ONLY invoices created at/after BillingConfig::since() are ever
 * reminded, so the pre-existing backlog is never texted. Day 0 (the billing day)
 * is covered by RunBillingCycle's "invoice generated" SMS, so this command skips
 * invoices created today.
 */
class SendBillingReminders extends Command
{
    protected $signature = 'billing:remind
        {--dry-run : Preview the messages without sending}
        {--force : Send even if reminders already ran today}';

    protected $description = 'Send daily payment reminders to customers with unpaid subscription invoices (new bills only).';

    public function handle(BillingReminderService $reminders): int
    {
        if (! BillingConfig::remindersEnabled()) {
            $this->info('Billing reminders are disabled.');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $today  = Carbon::today();

        // First ever run establishes the cutoff and sends nothing.
        $since = BillingConfig::since();
        if ($since === null) {
            $since = BillingConfig::ensureSince();
            $this->info('Initialised reminder cutoff to ' . $since->toDateTimeString() . ' — no reminders on first run.');
            return self::SUCCESS;
        }

        if (! $dryRun && ! $this->option('force') && BillingConfig::lastRun() === $today->toDateString()) {
            $this->info('Reminders already ran today — use --force to resend.');
            return self::SUCCESS;
        }

        $query = Invoice::with(['customer', 'subscription'])
            ->whereIn('status', ['Pending', 'Partial', 'Overdue'])
            ->where('balance_due', '>', 0)
            ->whereNotNull('subscription_id')
            ->where('created_at', '>=', $since)
            ->whereDate('created_at', '<', $today); // day 0 is the billing SMS

        $maxDays = BillingConfig::maxDays();
        if ($maxDays > 0) {
            $query->where('created_at', '>=', $today->copy()->subDays($maxDays));
        }

        $invoices = $query->get();

        // One SMS per customer, and only on a reminder day (day 1, due date, every
        // few days while overdue) for at least one of their unpaid invoices.
        $sent = 0;
        $skipped = 0;
        foreach ($invoices->groupBy('customer_id') as $customerInvoices) {
            $due = $customerInvoices->contains(fn($i) => BillingConfig::isReminderDay(
                Carbon::parse($i->created_at),
                $i->due_date ? Carbon::parse($i->due_date) : null,
                $today,
            ));
            if (! $due) {
                continue;
            }

            $message = $reminders->messageForCustomer($customerInvoices);
            if ($message === null) {
                $skipped++;
                continue;
            }

            $phone = $customerInvoices->first()->customer->phone;
            if ($dryRun) {
                $this->line("→ {$phone}: {$message}");
            } else {
                SendSmsJob::dispatch($phone, $message, SendSmsJob::PROFILE_REMINDER);
            }
            $sent++;
        }

        if (! $dryRun) {
            BillingConfig::setLastRun($today->toDateString());
            AuditService::log('Sent Billing Reminders', "Queued {$sent} reminder(s), skipped {$skipped}.");
        }

        $this->info(($dryRun ? 'DRY-RUN: would queue ' : 'Queued ') . "{$sent} reminder(s)" . ($skipped ? " (skipped {$skipped} with no phone)." : '.'));
        return self::SUCCESS;
    }
}
