<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\AuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileDuplicateInvoices extends Command
{
    /**
     * Dry-run by default; pass --apply to make changes.
     *
     * @var string
     */
    protected $signature = 'billing:reconcile-duplicates {--apply : Cancel duplicate invoices and backfill periods (default is a read-only dry-run)}';

    protected $description = 'Reconcile duplicate auto-generated invoices: keep one per billing period (preferring paid), cancel the rest, and flag paid-twice cases for manual refund.';

    public function handle()
    {
        $apply = (bool) $this->option('apply');

        $this->line($apply
            ? '<comment>APPLY mode — duplicates will be cancelled and periods backfilled.</comment>'
            : '<info>DRY-RUN — no changes will be made. Re-run with --apply to execute.</info>');
        $this->newLine();

        // All auto-generated invoices, with the data needed to group and decide.
        $autos = Invoice::with('customer.subscription')
            ->withCount('payments')
            ->where('notes', 'like', 'Auto-generated%')
            ->orderBy('id')
            ->get();

        // Group by (customer, subscription billing period of the invoice's creation date).
        $grouped = $autos->groupBy(function (Invoice $inv) {
            $sub = optional($inv->customer)->subscription;
            $period = $sub ? $sub->periodKey($inv->created_at) : $inv->created_at->format('Y-m');
            return $inv->customer_id . '|' . $period;
        });

        $stats = [
            'groups' => $grouped->count(),
            'dup_groups' => 0,
            'excess_cancelled' => 0,
            'paid_kept' => 0,
            'receivables_removed' => 0.0,
            'backfilled' => 0,
        ];
        $reviewGroups = [];
        $keepersToBackfill = collect();

        foreach ($grouped as $key => $invoices) {
            [$customerId, $period] = explode('|', $key, 2);

            $paidRows = $invoices->filter(fn (Invoice $i) => $i->status === 'Paid' || $i->payments_count > 0);

            // A customer who actually PAID more than one invoice for the same period
            // is a real double-charge -> refund/credit decision, never auto-touch.
            if ($paidRows->count() > 1) {
                $reviewGroups[] = [
                    'customer' => $customerId,
                    'period' => $period,
                    'invoices' => $invoices->map(fn (Invoice $i) => "{$i->invoice_number} ({$i->status})")->implode(', '),
                ];
                continue;
            }

            if ($invoices->count() <= 1) {
                // Legitimate singleton — still backfill so the unique index holds.
                $keepersToBackfill->push([$invoices->first(), $period]);
                continue;
            }

            $stats['dup_groups']++;

            $active = $invoices->reject(fn (Invoice $i) => $i->status === 'Cancelled');
            $keeper = $paidRows->first()
                ?? $active->sortBy('id')->first()
                ?? $invoices->sortBy('id')->first();

            if ($paidRows->count() === 1) {
                $stats['paid_kept']++;
            }

            $keepersToBackfill->push([$keeper, $period]);

            // Extras = active, non-keeper invoices.
            $extras = $active->reject(fn (Invoice $i) => $i->id === $keeper->id);

            foreach ($extras as $extra) {
                // Defensive: never cancel anything paid or with a payment.
                if ($extra->status === 'Paid' || $extra->payments_count > 0) {
                    continue;
                }

                $stats['excess_cancelled']++;
                $stats['receivables_removed'] += (float) $extra->balance_due;

                if ($apply) {
                    DB::transaction(function () use ($extra, $keeper) {
                        $extra->status = 'Cancelled';
                        $extra->balance_due = 0;
                        $extra->billing_period = null; // keep out of the unique index
                        $extra->notes = trim(($extra->notes ?? '')
                            . " | Auto-cancelled duplicate of {$keeper->invoice_number} by billing:reconcile-duplicates");
                        $extra->save();
                    });
                }
            }
        }

        // Backfill subscription_id + billing_period on the kept/singleton invoices.
        if ($apply) {
            foreach ($keepersToBackfill as [$invoice, $period]) {
                $subId = optional(optional($invoice->customer)->subscription)->id;
                if (! $subId) {
                    continue; // can't dedup-protect without a subscription; leave as manual
                }
                if ((int) $invoice->subscription_id === (int) $subId && $invoice->billing_period === $period) {
                    continue; // already correct (idempotent re-run)
                }
                $invoice->subscription_id = $subId;
                $invoice->billing_period = $period;
                $invoice->save();
                $stats['backfilled']++;
            }

            AuditService::log('Reconciled Duplicate Invoices',
                "Cancelled {$stats['excess_cancelled']} duplicates across {$stats['dup_groups']} groups; "
                . "backfilled {$stats['backfilled']}; " . count($reviewGroups) . ' paid-twice groups flagged.');
        }

        // ---- Report ----
        $this->newLine();
        $this->info('Summary');
        $this->table(['Metric', 'Value'], [
            ['Auto invoices scanned', $autos->count()],
            ['(customer, period) groups', $stats['groups']],
            ['Duplicate groups', $stats['dup_groups']],
            [($apply ? 'Duplicates cancelled' : 'Duplicates that WOULD be cancelled'), $stats['excess_cancelled']],
            ['Groups where a paid invoice was kept', $stats['paid_kept']],
            ['Phantom receivables removed (GHS)', number_format($stats['receivables_removed'], 2)],
            ['Keepers backfilled', $apply ? $stats['backfilled'] : 'n/a (dry-run)'],
            ['Paid-twice groups (manual refund)', count($reviewGroups)],
        ]);

        if (! empty($reviewGroups)) {
            $this->newLine();
            $this->warn('MANUAL REVIEW — customer paid more than one invoice for the same period (refund/credit):');
            $this->table(['Customer ID', 'Period', 'Invoices'],
                array_map(fn ($g) => [$g['customer'], $g['period'], $g['invoices']], $reviewGroups));
        }

        if (! $apply) {
            $this->newLine();
            $this->line('<info>No changes made. Re-run with --apply to execute.</info>');
        }

        return self::SUCCESS;
    }
}
