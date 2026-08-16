<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RunBillingCycle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices for due subscriptions (idempotent, one per billing period)';

    /**
     * Execute the console command.
     *
     * Safety guarantees against the historical double-billing bug:
     *  - One invoice per (subscription, billing_period): guarded in code AND by a
     *    unique DB index on invoices(subscription_id, billing_period).
     *  - next_billing_date is advanced to the next date strictly AFTER today, so a
     *    subscription that has fallen behind is realigned in a single run instead of
     *    being billed once per day until it catches up.
     *  - Each subscription is processed in its own transaction with a row lock.
     */
    public function handle()
    {
        $today = Carbon::today();

        $subscriptions = Subscription::where('status', 'Active')
            ->whereDate('next_billing_date', '<=', $today)
            ->pluck('id');

        $this->info("Found {$subscriptions->count()} subscriptions due for billing.");

        $billed = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($subscriptions as $subscriptionId) {
            try {
                DB::transaction(function () use ($subscriptionId, $today, &$billed, &$skipped) {
                    // Lock the row so overlapping runs can't process it twice.
                    $subscription = Subscription::with('customer')
                        ->lockForUpdate()
                        ->find($subscriptionId);

                    if (! $subscription || $subscription->status !== 'Active') {
                        return;
                    }

                    $billingDate = Carbon::parse($subscription->next_billing_date);

                    // Re-check due status under the lock (another run may have handled it).
                    if ($billingDate->gt($today)) {
                        $skipped++;
                        return;
                    }

                    $period = $subscription->periodKey($billingDate);

                    $alreadyBilled = Invoice::where('subscription_id', $subscription->id)
                        ->where('billing_period', $period)
                        ->exists();

                    if ($alreadyBilled) {
                        $skipped++;
                    } else {
                        $this->generateInvoice($subscription, $period, $today);
                        $billed++;
                    }

                    // Realign to the next future date regardless — no daily cascade.
                    $subscription->next_billing_date = $subscription->nextDateAfter($billingDate, $today);
                    $subscription->save();
                });
            } catch (\Throwable $e) {
                $failed++;
                $this->error("Failed billing subscription {$subscriptionId}: {$e->getMessage()}");
                Log::error('billing:run failed for subscription', [
                    'subscription_id' => $subscriptionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Billing cycle completed. Billed: {$billed}, Skipped (already billed): {$skipped}, Failed: {$failed}.");

        return self::SUCCESS;
    }

    protected function generateInvoice(Subscription $subscription, string $period, Carbon $today): void
    {
        $dueDate = $today->copy()->addDays((int) $subscription->due_date_offset_days);
        $invoiceNumber = 'INV-' . strtoupper(Str::random(8));

        $invoice = Invoice::create([
            'customer_id'     => $subscription->customer_id,
            'subscription_id' => $subscription->id,
            'billing_period'  => $period,
            'invoice_number'  => $invoiceNumber,
            'amount'          => $subscription->amount,
            'balance_due'     => $subscription->amount,
            'due_date'        => $dueDate,
            'status'          => 'Pending',
            'notes'           => 'Auto-generated invoice for period ' . $period,
        ]);

        // Notify customer.
        if ($subscription->customer && $subscription->customer->phone) {
            $amount = number_format($subscription->amount, 2);
            $msg = "New Invoice #{$invoice->invoice_number} Generated. Amount Due: GHS {$amount}. Due Date: {$dueDate->format('d M Y')}.";
            \App\Jobs\SendSmsJob::dispatch($subscription->customer->phone, $msg);
        }

        $this->info("Invoice {$invoice->invoice_number} generated for Customer ID: {$subscription->customer_id} (period {$period}).");
    }
}
