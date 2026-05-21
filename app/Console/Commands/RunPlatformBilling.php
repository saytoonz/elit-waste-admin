<?php

namespace App\Console\Commands;

use App\Services\PlatformBillingService;
use Illuminate\Console\Command;

class RunPlatformBilling extends Command
{
    protected $signature = 'platform:bill {--dry-run : Preview without writing}';
    protected $description = 'Generate platform-service invoices for due subscriptions and enforce overdue/suspensions';

    public function handle(PlatformBillingService $billing): int
    {
        if ($this->option('dry-run')) {
            $due = \App\Models\Platform\PlatformSubscription::dueForBilling()->where('auto_renew', true)->get();
            $this->info("DRY: {$due->count()} subscriptions would be invoiced today.");
            foreach ($due as $s) {
                $this->line(" - {$s->service?->name} qty={$s->quantity} {$s->currency} " . number_format($s->cycle_amount, 2));
            }
            return self::SUCCESS;
        }

        $created = $billing->runCycle();
        $this->info("Generated " . count($created) . " cycle invoice(s).");

        $stats = $billing->enforceOverdueAndSuspensions();
        $this->info("Marked {$stats['overdue']} invoice(s) overdue. Suspended {$stats['suspended']} subscription(s).");

        return self::SUCCESS;
    }
}
