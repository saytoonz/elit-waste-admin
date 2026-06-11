<?php

namespace App\Console\Commands;

use App\Models\SmsBroadcast;
use App\Services\SmsBroadcastService;
use Illuminate\Console\Command;

class DispatchScheduledSmsBroadcasts extends Command
{
    protected $signature = 'sms:dispatch-scheduled';

    protected $description = 'Launch SMS broadcasts whose scheduled time has arrived';

    public function handle(SmsBroadcastService $broadcasts): int
    {
        $due = SmsBroadcast::dueForDispatch()->orderBy('scheduled_at')->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled broadcasts due.');
            return self::SUCCESS;
        }

        foreach ($due as $broadcast) {
            $error = $broadcasts->launch($broadcast);
            if ($error) {
                $this->error("Broadcast #{$broadcast->id}: {$error}");
            } else {
                $this->info("Broadcast #{$broadcast->id} launched to {$broadcast->recipients_count} recipient(s).");
            }
        }

        return self::SUCCESS;
    }
}
