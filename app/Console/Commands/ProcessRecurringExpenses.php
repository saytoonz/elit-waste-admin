<?php

namespace App\Console\Commands;

use App\Http\Controllers\RecurringExpenseController;
use App\Models\RecurringExpense;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringExpenses extends Command
{
    protected $signature = 'expenses:run {--dry-run : Show what would happen without writing}';

    protected $description = 'Generate expenses from recurring schedules that are due';

    public function handle(): int
    {
        $today = Carbon::today();
        $dryRun = (bool) $this->option('dry-run');

        $recurring = RecurringExpense::where('is_active', true)
            ->whereDate('next_run_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            ->get();

        $this->info("Found {$recurring->count()} recurring expenses due.");

        $created = 0;
        foreach ($recurring as $r) {
            if ($dryRun) {
                $this->line("DRY: would generate {$r->name} (GHS {$r->amount}) for {$r->next_run_date->format('Y-m-d')}");
                continue;
            }

            // Catch-up loop: if next_run_date is far in the past, generate one per missed period
            while ($r->isDue()) {
                $expense = RecurringExpenseController::generateExpenseFrom($r);
                $r->last_run_date = $r->next_run_date;
                $r->advanceNextRunDate();
                $r->save();
                $this->info("Generated #{$expense->expense_number} for '{$r->name}'");
                $created++;
            }
        }

        $this->info($dryRun ? 'Dry run complete.' : "Done. Created {$created} expense(s).");
        return self::SUCCESS;
    }
}
