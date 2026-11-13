<?php

namespace App\Jobs;

use App\Models\Platform\PlatformSubscription;
use App\Models\Platform\SmsBundle;
use App\Models\SmsBroadcast;
use App\Models\SmsBroadcastRecipient;
use App\Models\SmsMessage;
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
    // Non-essential customer SMS (payment reminders): metered like PROFILE_CUSTOMER,
    // but may not dip into the bundle's reserve, which is kept for transactional SMS.
    public const PROFILE_REMINDER = 'reminder';

    /** Never retry automatically: a retry after a gateway timeout can double-send. */
    public $tries = 1;

    // Plain (non-promoted) properties with defaults, so jobs serialized before these
    // existed still unserialize cleanly.
    public string $type = 'other';
    public ?int $customerId = null;
    public ?int $smsMessageId = null;

    public function __construct(
        public string $phone,
        public string $message,
        public string $profile = self::PROFILE_CUSTOMER,
        public ?int $broadcastId = null,
        public ?int $recipientId = null,
    ) {
    }

    /**
     * Log + queue an SMS. Preferred entry point: the message shows up in the SMS
     * log as "Queued" immediately, even before the worker picks it up.
     */
    public static function send(
        string $phone,
        string $message,
        string $type = 'other',
        ?int $customerId = null,
        string $profile = self::PROFILE_CUSTOMER,
        ?int $broadcastId = null,
        ?int $recipientId = null,
    ): SmsMessage {
        $log = SmsMessage::create([
            'phone'            => $phone,
            'message'          => $message,
            'type'             => $type,
            'profile'          => $profile,
            'status'           => 'Queued',
            'customer_id'      => $customerId,
            'sms_broadcast_id' => $broadcastId,
        ]);

        $job = new static($phone, $message, $profile, $broadcastId, $recipientId);
        $job->type = $type;
        $job->customerId = $customerId;
        $job->smsMessageId = $log->id;
        dispatch($job);

        return $log;
    }

    public function handle(MyCSMSService $smsService): void
    {
        Log::info("Processing SMS Job ({$this->profile}) for {$this->phone}");

        $this->logRow()?->increment('attempts');

        if ($this->profile === self::PROFILE_PROVIDER) {
            // Provider/system SMS — unlimited quota, send directly
            $sent = $this->dispatchToGateway($smsService);
            $this->record($sent ? 'sent' : 'failed', 0, $sent ? null : $smsService->lastResult['error'] ?? null, $smsService);
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
                $this->record('skipped', 0, 'No active SMS bundle — pay the SMS invoice.');
                return;
            }

            Log::info("SMS (no bundle, no subscription — legacy path) sending unmetered to {$this->phone}");
            $sent = $this->dispatchToGateway($smsService);
            $this->record($sent ? 'sent' : 'failed', 0, $sent ? null : $smsService->lastResult['error'] ?? null, $smsService);
            return;
        }

        // 1 credit per 160 characters (161 chars = 2 credits, etc.)
        $credits = SmsBundle::creditsFor($this->message);

        $reserve = $this->profile === self::PROFILE_REMINDER ? $bundle->reminderReserve() : 0;

        if (!$bundle->consume($credits, $reserve)) {
            $why = $reserve > 0 ? " above the {$reserve}-credit reserve for transactional SMS" : '';
            Log::warning("SMS skipped ({$this->profile}) — bundle #{$bundle->id} lacks {$credits} credit(s){$why}, or is exhausted/expired ({$this->phone})");
            $this->record('skipped', 0, $reserve > 0
                ? "Needs {$credits} credit(s) — only the reserve for payment/invoice SMS is left."
                : "Needs {$credits} credit(s) — bundle exhausted, expired, or short.");
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

        $this->record($sent ? 'sent' : 'failed', $sent ? $credits : 0, $sent ? null : $smsService->lastResult['error'] ?? null, $smsService);
    }

    /** The job crashed (exception/timeout) — make sure the log doesn't stay "Queued". */
    public function failed(?\Throwable $e): void
    {
        $this->record('failed', 0, 'Job error: ' . ($e?->getMessage() ?? 'unknown'));
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

    /** The SMS log row for this job, created on the fly for jobs queued before logging existed. */
    protected function logRow(): ?SmsMessage
    {
        try {
            if ($this->smsMessageId && ($row = SmsMessage::find($this->smsMessageId))) {
                return $row;
            }

            $row = SmsMessage::create([
                'phone'            => $this->phone,
                'message'          => $this->message,
                'type'             => $this->broadcastId ? 'broadcast' : $this->type,
                'profile'          => $this->profile,
                'status'           => 'Queued',
                'customer_id'      => $this->customerId,
                'sms_broadcast_id' => $this->broadcastId,
            ]);
            $this->smsMessageId = $row->id;
            return $row;
        } catch (\Throwable $e) {
            Log::warning('SMS log write failed: ' . $e->getMessage());
            return null;
        }
    }

    /** Record the outcome on the SMS log and, for broadcasts, on the recipient row. */
    protected function record(string $outcome, int $credits = 0, ?string $error = null, ?MyCSMSService $gateway = null): void
    {
        $this->logRow()?->markOutcome(ucfirst($outcome), $credits, $error, $gateway?->lastResult);
        $this->recordBroadcastOutcome($outcome, $credits, $error);
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
                    'error'   => $error ? mb_substr($error, 0, 255) : null,
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
