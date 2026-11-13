<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Log of every SMS sent (or attempted) through the gateway. "Sent" means the
 * gateway accepted the message; handset delivery is not tracked.
 */
class SmsMessage extends Model
{
    public const TYPES = [
        'payment'        => 'Payment confirmation',
        'invoice'        => 'New invoice',
        'reminder'       => 'Payment reminder',
        'broadcast'      => 'Broadcast',
        'welcome'        => 'Welcome',
        'admin_reminder' => 'Admin billing reminder',
        'lead_alert'     => 'Website lead alert',
        'other'          => 'Other',
    ];

    public const STATUSES = ['Queued', 'Sent', 'Failed', 'Skipped'];

    protected $fillable = [
        'phone', 'message', 'type', 'profile', 'status', 'credits', 'attempts', 'error',
        'gateway_status', 'gateway_message_id', 'gateway_state', 'gateway_cost', 'gateway_response', 'customer_id', 'sms_broadcast_id', 'sent_at',
    ];

    protected $casts = [
        'credits'        => 'integer',
        'attempts'       => 'integer',
        'gateway_status' => 'integer',
        'sent_at'        => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function broadcast()
    {
        return $this->belongsTo(SmsBroadcast::class, 'sms_broadcast_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    /** Record the outcome of one gateway attempt. */
    public function markOutcome(string $status, int $credits = 0, ?string $error = null, ?array $gateway = null): void
    {
        $this->update([
            'status'           => $status,
            'credits'          => $credits,
            'error'            => $error ? mb_substr($error, 0, 255) : null,
            'gateway_status'     => $gateway['status'] ?? $this->gateway_status,
            'gateway_message_id' => $gateway['message_id'] ?? $this->gateway_message_id,
            'gateway_state'      => $gateway['state'] ?? $this->gateway_state,
            'gateway_cost'       => is_numeric($gateway['cost'] ?? null) ? $gateway['cost'] : $this->gateway_cost,
            'gateway_response' => isset($gateway['body']) ? mb_substr((string) $gateway['body'], 0, 2000) : $this->gateway_response,
            'sent_at'          => $status === 'Sent' ? now() : null,
        ]);
    }
}
