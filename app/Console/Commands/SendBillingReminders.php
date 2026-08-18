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
 * Daily SMS reminder to customers with an unpaid subscription bill — sends every
 * day from the day after billing until the invoice is paid (including overdue).
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

        $sent = 0;
        $skipped = 0;
        foreach ($invoices as $invoice) {
            $message = $reminders->messageFor($invoice);
            if ($message === null) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("→ {$invoice->customer->phone}: {$message}");
            } else {
                SendSmsJob::dispatch($invoice->customer->phone, $message);
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
