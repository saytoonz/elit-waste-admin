<?php

namespace App\Console\Commands;

use App\Jobs\SendSmsJob;
use App\Models\Platform\SmsBundle;
use App\Services\AuditService;
use App\Services\PlatformReminderService;
use App\Support\PlatformConfig;
use Illuminate\Console\Command;

class SendPlatformReminders extends Command
{
    protected $signature = 'platform:remind
        {--force : Send even if a reminder was already sent today}
        {--dry-run : Preview the message and credit cost without sending}';

    protected $description = 'Daily SMS to the admin: unpaid platform invoices, renewals, grace periods, SMS bundle expiry (uses SMS credits)';

    public function handle(PlatformReminderService $reminders): int
    {
        if (!PlatformConfig::remindersEnabled()) {
            $this->info('Billing reminders are disabled in Platform Settings.');
            return self::SUCCESS;
        }

        $phone = $reminders->reminderPhone();
        if (!$phone) {
            $this->warn('No reminder phone set — add a company phone or reminder phone in Platform Settings.');
            return self::SUCCESS;
        }

        $message = $reminders->compose();
        if ($message === null) {
            $this->info('Nothing to remind about today.');
            return self::SUCCESS;
        }

        $credits = SmsBundle::creditsFor($message);

        if ($this->option('dry-run')) {
            $this->info("DRY: would send to {$phone} (~{$credits} credit(s)):");
            $this->line($message);
            return self::SUCCESS;
        }

        $today = now()->toDateString();
        if (!$this->option('force') && PlatformConfig::reminderLastSent() === $today) {
            $this->info('Reminder already sent today — use --force to resend.');
            return self::SUCCESS;
        }

        // Customer profile (default) → consumes the customer's SMS credits.
        SendSmsJob::dispatch($phone, $message);
        PlatformConfig::setReminderLastSent($today);
        AuditService::log('Sent Platform Billing Reminder', "to {$phone}, ~{$credits} credit(s)");

        $this->info("Queued reminder to {$phone} (~{$credits} credit(s)).");
        return self::SUCCESS;
    }
}
