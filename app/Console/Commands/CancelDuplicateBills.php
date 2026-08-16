<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Broader duplicate cleanup than billing:reconcile-duplicates.
 *
 * reconcile-duplicates only touches Auto-generated invoices and groups by the
 * subscription's billing cycle. This command catches the leftovers it can't:
 *  - "forced bills" / manually-created duplicate invoices (any notes), and
 *  - cases where the billing_cycle field is unreliable,
 * by grouping on CALENDAR MONTH + AMOUNT instead.
 *
 * Safety: only PENDING invoices with no payment are ever cancelled. Paid,
 * Partial, and already-Cancelled invoices are never modified. A group where the
 * customer PAID more than once is skipped and flagged for a manual refund.
 */
class CancelDuplicateBills extends Command
{
    protected $signature = 'billing:cancel-duplicate-bills
                            {--apply : Cancel the pending duplicates (default is a read-only dry-run)}
                            {--any-amount : Match duplicates by month only, ignoring the amount}';

    protected $description = 'Cancel PENDING duplicate invoices (auto or manual/forced) when the customer has another invoice for the same calendar month (and amount). Keeps the paid/earliest one; never touches paid or partial invoices.';

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $anyAmount = (bool) $this->option('any-amount');

        $this->line($apply
            ? '<comment>APPLY mode — pending duplicates will be cancelled.</comment>'
            : '<info>DRY-RUN — no changes will be made. Add --apply to execute.</info>');
        $this->line('Matching: same customer + same calendar month' . ($anyAmount ? ' (amount IGNORED).' : ' + same amount.'));
        $this->newLine();

        $invoices = Invoice::withCount('payments')->orderBy('id')->get();

        $grouped = $invoices->groupBy(function (Invoice $inv) use ($anyAmount) {
            $month = $inv->created_at->format('Y-m');
            $amount = number_format((float) $inv->amount, 2, '.', '');
            return $anyAmount
                ? $inv->customer_id . '|' . $month
                : $inv->customer_id . '|' . $month . '|' . $amount;
        });

        $stats = ['groups' => 0, 'cancelled' => 0, 'removed' => 0.0, 'review' => 0];
        $affected = [];
        $reviewList = [];

        foreach ($grouped as $group) {
            if ($group->count() <= 1) {
                continue;
            }

            $paid    = $group->filter(fn (Invoice $i) => $i->status === 'Paid' || $i->payments_count > 0);
            $pending = $group->filter(fn (Invoice $i) => $i->status === 'Pending' && $i->payments_count == 0);

            if ($pending->isEmpty()) {
                continue; // nothing cancellable in this group
            }

            // Customer paid the same charge more than once -> refund/credit, never auto-touch.
            if ($paid->count() > 1) {
                $stats['review']++;
                $reviewList[] = $group->first()->customer_id . ' ' . $group->first()->created_at->format('Y-m')
                    . ' :: ' . $group->map(fn (Invoice $i) => $i->invoice_number . '(' . $i->status . ')')->implode(', ');
                continue;
            }

            $keeper = $paid->first()
                ?? $group->reject(fn (Invoice $i) => $i->status === 'Cancelled')->sortBy('id')->first()
                ?? $group->sortBy('id')->first();

            $toCancel = $pending->reject(fn (Invoice $i) => $i->id === $keeper->id);
            if ($toCancel->isEmpty()) {
                continue;
            }

            $stats['groups']++;

            foreach ($toCancel as $inv) {
                $stats['cancelled']++;
                $stats['removed'] += (float) $inv->balance_due;
                $affected[] = [
                    $inv->customer_id,
                    $inv->created_at->format('Y-m-d'),
                    $inv->invoice_number,
                    number_format($inv->amount, 2),
                    'keep ' . $keeper->invoice_number . ' (' . $keeper->status . ')',
                ];

                if ($apply) {
                    DB::transaction(function () use ($inv, $keeper) {
                        $inv->status = 'Cancelled';
                        $inv->balance_due = 0;
                        $inv->notes = trim(($inv->notes ?? '')
                            . " | Auto-cancelled same-month duplicate of {$keeper->invoice_number} by billing:cancel-duplicate-bills");
                        $inv->save();
                    });
                }
            }
        }

        if ($apply) {
            AuditService::log('Cancelled Duplicate Bills',
                "Cancelled {$stats['cancelled']} pending same-month duplicates across {$stats['groups']} groups (GHS "
                . number_format($stats['removed'], 2) . "). {$stats['review']} paid-twice groups skipped.");
        }

        $this->info('Summary');
        $this->table(['Metric', 'Value'], [
            ['Duplicate groups acted on', $stats['groups']],
            [($apply ? 'Pending duplicates cancelled' : 'Pending duplicates that WOULD be cancelled'), $stats['cancelled']],
            ['Receivables removed (GHS)', number_format($stats['removed'], 2)],
            ['Paid-twice groups (skipped — manual refund)', $stats['review']],
        ]);

        if (! empty($affected)) {
            $this->newLine();
            $this->line($apply ? 'Cancelled these:' : 'WOULD cancel these:');
            $this->table(['Customer', 'Date', 'Invoice', 'Amount', 'Keeper'], $affected);
        }

        if (! empty($reviewList)) {
            $this->newLine();
            $this->warn('Paid-twice groups (skipped — refund/credit manually):');
            foreach ($reviewList as $r) {
                $this->line('  ' . $r);
            }
        }

        if (! $apply) {
            $this->newLine();
            $this->line('<info>No changes made. Re-run with --apply to execute.</info>');
        }

        return self::SUCCESS;
    }
}
