<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
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
    protected $description = 'Generate invoices for due subscriptions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        $subscriptions = Subscription::where('status', 'Active')
            ->whereDate('next_billing_date', '<=', $today)
            ->with('customer')
            ->get();

        $this->info("Found {$subscriptions->count()} subscriptions due for billing.");

        foreach ($subscriptions as $subscription) {
            $this->generateInvoice($subscription);
            $this->updateNextBillingDate($subscription);
        }

        $this->info('Billing cycle completed.');
    }

    protected function generateInvoice(Subscription $subscription)
    {
        $dueDate = Carbon::today()->addDays($subscription->due_date_offset_days);
        $invoiceNumber = 'INV-' . strtoupper(Str::random(8)); // In real app, use sequential or ID based

        Invoice::create([
            'customer_id' => $subscription->customer_id,
            'invoice_number' => $invoiceNumber,
            'amount' => $subscription->amount,
            'balance_due' => $subscription->amount, // Full amount initially
            'due_date' => $dueDate,
            'status' => 'Pending',
            'notes' => 'Auto-generated invoice for period ' . Carbon::today()->format('M Y'),
        ]);

        // Send SMS
        if ($subscription->customer && $subscription->customer->phone) {
             $amount = number_format($subscription->amount, 2);
             // We can use due_date from created invoice or calculate it again.
             // $dueDate defined above.
             $msg = "New Invoice #{$invoiceNumber} Generated. Amount Due: GHS {$amount}. Due Date: {$dueDate->format('d M Y')}.";
             \App\Jobs\SendSmsJob::dispatch($subscription->customer->phone, $msg);
        }

        $this->info("Invoice generated for Customer ID: {$subscription->customer_id}");
    }

    protected function updateNextBillingDate(Subscription $subscription)
    {
        $currentDate = Carbon::parse($subscription->next_billing_date);

        if ($subscription->billing_cycle === 'Weekly') {
            $nextDate = $currentDate->addWeek();
        } elseif ($subscription->billing_cycle === 'Monthly') {
            $nextDate = $currentDate->addMonth();
        } elseif ($subscription->billing_cycle === 'Quarterly') {
            $nextDate = $currentDate->addQuarter();
        } else {
            $nextDate = $currentDate->addMonth(); // Default
        }

        $subscription->update(['next_billing_date' => $nextDate]);
    }
}
