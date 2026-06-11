<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsBroadcastRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'sms_broadcast_id',
        'customer_id',
        'name',
        'phone',
        'message',
        'credits',
        'status',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'credits' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function broadcast()
    {
        return $this->belongsTo(SmsBroadcast::class, 'sms_broadcast_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isRetryable(): bool
    {
        return in_array($this->status, ['Failed', 'Skipped'], true);
    }
}
