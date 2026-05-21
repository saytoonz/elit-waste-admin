<?php

namespace App\Jobs;

use App\Models\Platform\PlatformSubscription;
use App\Models\Platform\SmsBundle;
use App\Services\MyCSMSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const PROFILE_CUSTOMER = 'customer';
    public const PROFILE_PROVIDER = 'provider';

    public function __construct(
        public string $phone,
        public string $message,
        public string $profile = self::PROFILE_CUSTOMER,
    ) {
    }

    public function handle(MyCSMSService $smsService): void
    {
        Log::info("Processing SMS Job ({$this->profile}) for {$this->phone}");

        if ($this->profile === self::PROFILE_PROVIDER) {
            // Provider/system SMS — unlimited quota, send directly
            $this->dispatchToGateway($smsService);
            return;
        }

        // Customer profile — must be backed by an active SMS bundle
        $bundle = SmsBundle::findActive();

        if (!$bundle) {
            // No active bundle. Backward-compatible behavior: if there's NO SMS
            // subscription at all on this install, allow the send so the system
            // keeps working until the customer subscribes + pays. If there's an
            // SMS subscription but no active bundle (unpaid invoice), block.
            $hasSmsSubscription = PlatformSubscription::query()
                ->whereHas('service', fn($q) => $q->where('type', 'SMS'))
                ->whereIn('status', ['Active', 'Paused', 'Suspended'])
                ->exists();

            if ($hasSmsSubscription) {
                Log::warning("SMS skipped — no active bundle. Pay the SMS invoice to restore service. ({$this->phone})");
                return;
            }

            Log::info("SMS (no bundle, no subscription — legacy path) sending unmetered to {$this->phone}");
            $this->dispatchToGateway($smsService);
            return;
        }

        if (!$bundle->consumeOne()) {
            Log::warning("SMS skipped — bundle #{$bundle->id} exhausted or expired ({$this->phone})");
            return;
        }

        $sent = $this->dispatchToGateway($smsService);

        if (!$sent) {
            // Refund the consumed unit so we don't penalize the customer for an upstream failure
            $bundle->decrement('quantity_used');
            Log::info("Refunded 1 SMS to bundle #{$bundle->id} after gateway failure");
        }
    }

    /**
     * Returns true if the gateway accepted the message.
     */
    protected function dispatchToGateway(MyCSMSService $smsService): bool
    {
        $success = $smsService->send($this->phone, $this->message);
        if (!$success) {
            Log::warning("SMS Job failed at gateway for {$this->phone}");
        }
        return $success;
    }
}
