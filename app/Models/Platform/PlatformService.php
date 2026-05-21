<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'unit_price',
        'currency',
        'billing_cycle',
        'is_quantity_based',
        'unit_label',
        'default_quantity',
        'min_quantity',
        'sms_messages_per_unit',
        'grace_days',
        'description',
        'features',
        'customer_addable',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'unit_price'            => 'decimal:2',
        'is_quantity_based'     => 'boolean',
        'customer_addable'      => 'boolean',
        'is_active'             => 'boolean',
        'features'              => 'array',
        'default_quantity'      => 'integer',
        'min_quantity'          => 'integer',
        'sms_messages_per_unit' => 'integer',
        'grace_days'            => 'integer',
        'sort_order'            => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(PlatformSubscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        $suffix = match ($this->billing_cycle) {
            'Monthly' => '/mo',
            'Quarterly' => '/qtr',
            'Yearly' => '/yr',
            default => '',
        };
        $perUnit = $this->is_quantity_based && $this->unit_label ? " per {$this->unit_label}" : '';
        return "{$this->currency} " . number_format($this->unit_price, 2) . $suffix . $perUnit;
    }
}
