<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\AuditService;
use App\Support\BillingConfig;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * One-time (re-runnable) alignment of every active subscription onto the standard
 * schedule: Monthly → the 28th, Weekly → Saturday, with a 2-day payment window.
 *
 * Safety: a subscription's next_billing_date is only ever moved to an anchor date
 * ON OR AFTER max(current date, today) — it never moves earlier, so no customer is
 * billed sooner than they already would have been.
 */
class RealignBillingSchedule extends Command
{
    protected $signature = 'billing:realign-schedule
        {--apply : Persist the changes (default is a read-only dry-run)}';

    protected $description = 'Align active subscriptions to the standard schedule (Monthly=28th, Weekly=Saturday, 2-day due window).';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $today = Carbon::today();

        $this->line($apply
            ? '<comment>APPLY mode — subscription schedules will be updated.</comment>'
            : '<info>DRY-RUN — no changes. Add --apply to execute.</info>');
        $this->newLine();

        $subs = Subscription::where('status', 'Active')->with('customer')->orderBy('id')->get();

        $rows = [];
        $changed = 0;

        foreach ($subs as $sub) {
            $current = $sub->next_billing_date ? Carbon::parse($sub->next_billing_date) : $today->copy();
            $floor   = $current->gt($today) ? $current->copy() : $today->copy();
            $new     = $sub->anchorOnOrAfter($floor);

            $offsetChanged = (int) $sub->due_date_offset_days !== 2;
            $dateChanged   = $new->toDateString() !== $current->toDateString();

            if ($dateChanged || $offsetChanged) {
                $changed++;
                $rows[] = [
                    $sub->customer->name ?? ('Customer #' . $sub->customer_id),
                    $sub->billing_cycle,
                    $current->toDateString(),
                    $new->toDateString() . ' (' . $new->format('D') . ')',
                    ((int) $sub->due_date_offset_days) . ' → 2',
                ];

                if ($apply) {
                    $sub->next_billing_date = $new->toDateString();
                    $sub->due_date_offset_days = 2;
                    $sub->save();
                }
            }
        }

        if ($apply) {
            BillingConfig::setSince(now()); // reminders only cover invoices generated from here on
            AuditService::log('Realigned Billing Schedule', "Updated {$changed} subscription(s) to standard 28th/Saturday schedule.");
        }

        $this->info(($apply ? 'Updated ' : 'Would update ') . "{$changed} of {$subs->count()} active subscription(s).");
        if (! empty($rows)) {
            $this->newLine();
            $this->table(['Customer', 'Cycle', 'Old date', 'New date', 'Due offset'], $rows);
        }
        if (! $apply) {
            $this->newLine();
            $this->line('<info>No changes made. Re-run with --apply to execute.</info>');
        }

        return self::SUCCESS;
    }
}
