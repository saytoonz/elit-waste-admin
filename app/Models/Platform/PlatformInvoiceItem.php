<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_invoice_id',
        'platform_subscription_id',
        'platform_service_id',
        'description',
        'quantity',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(PlatformInvoice::class, 'platform_invoice_id');
    }

    public function subscription()
    {
        return $this->belongsTo(PlatformSubscription::class, 'platform_subscription_id');
    }

    public function service()
    {
        return $this->belongsTo(PlatformService::class, 'platform_service_id');
    }
}
