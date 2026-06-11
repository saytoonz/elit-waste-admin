<?php

namespace App\Jobs;

use App\Models\Platform\PlatformSubscription;
use App\Models\Platform\SmsBundle;
use App\Models\SmsBroadcast;
use App\Models\SmsBroadcastRecipient;
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
        public ?int $broadcastId = null,
        public ?int $recipientId = null,
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
                $this->recordBroadcastOutcome('skipped', 0, 'No active SMS bundle — pay the SMS invoice.');
                return;
            }

            Log::info("SMS (no bundle, no subscription — legacy path) sending unmetered to {$this->phone}");
            $sent = $this->dispatchToGateway($smsService);
            $this->recordBroadcastOutcome($sent ? 'sent' : 'failed', 0, $sent ? null : 'Gateway rejected the message.');
            return;
        }

        // 1 credit per 160 characters (161 chars = 2 credits, etc.)
        $credits = SmsBundle::creditsFor($this->message);

        if (!$bundle->consume($credits)) {
            Log::warning("SMS skipped — bundle #{$bundle->id} lacks {$credits} credit(s), or is exhausted/expired ({$this->phone})");
            $this->recordBroadcastOutcome('skipped', 0, "Needs {$credits} credit(s) — bundle exhausted, expired, or short.");
            return;
        }

        $sent = $this->dispatchToGateway($smsService);

        if (!$sent) {
            // Refund the consumed credits so we don't penalize the customer for an upstream failure
            $bundle->decrement('quantity_used', $credits);
            $bundle->refresh();
            if ($bundle->status === 'Exhausted' && $bundle->quantity_used < $bundle->quantity_total) {
                $bundle->update(['status' => 'Active']);
            }
            Log::info("Refunded {$credits} SMS credit(s) to bundle #{$bundle->id} after gateway failure");
        }

        $this->recordBroadcastOutcome($sent ? 'sent' : 'failed', $sent ? $credits : 0, $sent ? null : 'Gateway rejected the message.');
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

    /**
     * If this send belongs to a broadcast, record the outcome on the recipient row
     * and resync the broadcast counters (drift-free under retries). Falls back to
     * plain counter increments for jobs queued before per-recipient tracking.
     */
    protected function recordBroadcastOutcome(string $outcome, int $credits = 0, ?string $error = null): void
    {
        if (!$this->broadcastId) return;

        $broadcast = SmsBroadcast::find($this->broadcastId);
        if (!$broadcast) return;

        if ($this->recipientId) {
            $recipient = SmsBroadcastRecipient::find($this->recipientId);
            if ($recipient) {
                $recipient->update([
                    'status'  => ucfirst($outcome), // Sent | Failed | Skipped
                    'credits' => $credits,
                    'error'   => $error,
                    'sent_at' => $outcome === 'sent' ? now() : null,
                ]);
            }
            $broadcast->syncCountersFromRecipients();
            return;
        }

        // Legacy jobs without a recipient row
        $column = match ($outcome) {
            'sent'    => 'sent_count',
            'failed'  => 'failed_count',
            'skipped' => 'skipped_count',
            default   => null,
        };
        if (!$column) return;

        $broadcast->increment($column);
        if ($credits > 0) {
            $broadcast->increment('credits_used', $credits);
        }

        $broadcast->refresh();
        if ($broadcast->processed_count >= $broadcast->recipients_count && $broadcast->status !== 'Completed') {
            $broadcast->update(['status' => 'Completed']);
        }
    }
}
