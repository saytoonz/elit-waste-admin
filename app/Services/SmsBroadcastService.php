<?php

namespace App\Services;

use App\Jobs\SendSmsJob;
use App\Models\Customer;
use App\Models\Platform\PlatformSubscription;
use App\Models\Platform\SmsBundle;
use App\Models\SmsBroadcast;
use Illuminate\Support\Facades\Log;

class SmsBroadcastService
{
    /**
     * Resolve the audience filters to customers that have a phone number,
     * deduplicated by phone. Each customer carries an `outstanding` sum for {balance}.
     */
    public function resolveRecipients(array $f)
    {
        $query = Customer::query()
            ->withSum(['invoices as outstanding' => fn($q) => $q->whereNotIn('status', ['Paid', 'Cancelled'])], 'balance_due')
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        if (empty($f['include_inactive'])) {
            $query->where('is_active', true);
        }

        if (($f['audience'] ?? 'all') === 'zones') {
            $query->whereIn('zone_id', $f['zone_ids'] ?? []);
        } elseif (($f['audience'] ?? 'all') === 'customers') {
            $query->whereIn('id', $f['customer_ids'] ?? []);
        }

        if (!empty($f['types'])) {
            $query->whereIn('type', $f['types']);
        }

        $customers = $query->orderBy('name')->get();

        if (!empty($f['debtors_only'])) {
            $customers = $customers->filter(fn($c) => (float) ($c->outstanding ?? 0) > 0);
        }

        return $customers->unique(fn($c) => preg_replace('/\s+/', '', $c->phone))->values();
    }

    /** Replace {name} and {balance} placeholders with the customer's values. */
    public function personalize(string $message, Customer $customer): string
    {
        return str_replace(
            ['{name}', '{balance}'],
            [$customer->name, 'GHS ' . number_format((float) ($customer->outstanding ?? 0), 2)],
            $message
        );
    }

    /**
     * Resolve recipients + render messages + exact credit cost for a message/filters pair.
     * Returns [recipients, messages, creditsNeeded].
     */
    public function prepare(string $message, array $filters): array
    {
        $recipients = $this->resolveRecipients($filters);
        $messages = $recipients->map(fn($c) => [
            'phone' => $c->phone,
            'body'  => $this->personalize($message, $c),
        ]);
        $creditsNeeded = $messages->sum(fn($m) => SmsBundle::creditsFor($m['body']));

        return [$recipients, $messages, $creditsNeeded];
    }

    /**
     * Quota gate shared by send-now and scheduled dispatch.
     * Returns an error string when the broadcast can't go out, null when it can.
     */
    public function quotaError(int $creditsNeeded): ?string
    {
        $bundle = SmsBundle::findActive();
        if ($bundle) {
            if ($bundle->remaining < $creditsNeeded) {
                return "Not enough SMS credits: needs {$creditsNeeded} but only {$bundle->remaining} remain.";
            }
            return null;
        }

        $hasSmsSubscription = PlatformSubscription::query()
            ->whereHas('service', fn($q) => $q->where('type', 'SMS'))
            ->whereIn('status', ['Active', 'Paused', 'Suspended'])
            ->exists();

        return $hasSmsSubscription
            ? 'No active SMS bundle — pay the SMS invoice to restore sending.'
            : null; // legacy unmetered install
    }

    /**
     * Launch a Draft/Scheduled/Failed broadcast NOW: re-resolve the audience against
     * current data, re-check credits, dispatch one job per recipient, flip to Processing.
     * Returns an error string on failure (broadcast marked Failed), null on success.
     */
    public function launch(SmsBroadcast $broadcast): ?string
    {
        [$recipients, $messages, $creditsNeeded] = $this->prepare($broadcast->message, $broadcast->filters ?? []);

        if ($recipients->isEmpty()) {
            $broadcast->update(['status' => 'Failed', 'failure_reason' => 'No customers with a phone number match the filters.']);
            return $broadcast->failure_reason;
        }

        if ($error = $this->quotaError($creditsNeeded)) {
            $broadcast->update(['status' => 'Failed', 'failure_reason' => $error]);
            Log::warning("SMS Broadcast #{$broadcast->id} failed to launch: {$error}");
            return $error;
        }

        $broadcast->update([
            'recipients_count'  => $recipients->count(),
            'credits_estimated' => $creditsNeeded,
            'sent_count'        => 0,
            'failed_count'      => 0,
            'skipped_count'     => 0,
            'credits_used'      => 0,
            'status'            => 'Processing',
            'failure_reason'    => null,
        ]);

        foreach ($messages as $m) {
            SendSmsJob::dispatch($m['phone'], $m['body'], SendSmsJob::PROFILE_CUSTOMER, $broadcast->id);
        }

        AuditService::log('Launched SMS Broadcast', "#{$broadcast->id} to {$recipients->count()} customer(s), ~{$creditsNeeded} credit(s)");
        return null;
    }
}
