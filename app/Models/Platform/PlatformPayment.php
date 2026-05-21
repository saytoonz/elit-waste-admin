<?php

namespace App\Models\Platform;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_invoice_id',
        'reference',
        'amount',
        'currency',
        'status',
        'channel',
        'paid_at',
        'recorded_by',
        'metadata',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'metadata' => 'array',
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(PlatformInvoice::class, 'platform_invoice_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
