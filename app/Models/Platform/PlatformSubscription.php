<?php

namespace App\Models\Platform;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_service_id',
        'quantity',
        'unit_price',
        'currency',
        'billing_cycle',
        'status',
        'start_date',
        'next_billing_date',
        'last_billed_date',
        'auto_renew',
        'force_payment',
        'grace_days',
        'suspended_at',
        'suspension_reason',
        'metadata',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'next_billing_date' => 'date',
        'last_billed_date'  => 'date',
        'suspended_at'      => 'datetime',
        'auto_renew'        => 'boolean',
        'force_payment'     => 'boolean',
        'unit_price'        => 'decimal:2',
        'metadata'          => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(PlatformService::class, 'platform_service_id');
    }

    public function invoiceItems()
    {
        return $this->hasMany(PlatformInvoiceItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeDueForBilling($query, ?Carbon $on = null)
    {
        $on = $on ?: Carbon::today();
        return $query->whereIn('status', ['Active', 'Suspended'])
                     ->whereDate('next_billing_date', '<=', $on);
    }

    public function getMonthlyEquivalentAttribute(): float
    {
        $cycleAmount = (float) $this->unit_price * $this->quantity;
        return match ($this->billing_cycle) {
            'Monthly' => $cycleAmount,
            'Quarterly' => $cycleAmount / 3,
            'Yearly' => $cycleAmount / 12,
            default => $cycleAmount,
        };
    }

    public function getYearlyEquivalentAttribute(): float
    {
        $cycleAmount = (float) $this->unit_price * $this->quantity;
        return match ($this->billing_cycle) {
            'Monthly' => $cycleAmount * 12,
            'Quarterly' => $cycleAmount * 4,
            'Yearly' => $cycleAmount,
            default => $cycleAmount * 12,
        };
    }

    public function getCycleAmountAttribute(): float
    {
        return (float) $this->unit_price * $this->quantity;
    }

    public function cycleLabelFor(int $count = 1): string
    {
        $unit = match ($this->billing_cycle) {
            'Monthly' => 'month',
            'Quarterly' => 'quarter',
            'Yearly' => 'year',
            default => 'cycle',
        };
        return $count > 1 ? "{$count} {$unit}s" : "1 {$unit}";
    }

    public function advanceBillingDate(int $cycles = 1): void
    {
        $base = Carbon::parse($this->next_billing_date);
        $this->next_billing_date = match ($this->billing_cycle) {
            'Monthly' => $base->copy()->addMonths($cycles),
            'Quarterly' => $base->copy()->addMonths(3 * $cycles),
            'Yearly' => $base->copy()->addYears($cycles),
            default => $base->copy()->addMonths($cycles),
        };
    }

    public function isOverdue(): bool
    {
        return Carbon::parse($this->next_billing_date)->isPast();
    }

    public function isPastGrace(): bool
    {
        if (!$this->isOverdue()) return false;
        $cutoff = Carbon::parse($this->next_billing_date)->addDays($this->grace_days);
        return Carbon::today()->greaterThan($cutoff);
    }

    public function shouldBlockAccess(): bool
    {
        if (!$this->force_payment) return false;
        if ($this->status === 'Cancelled') return false;
        // Block if past grace AND there is an unpaid invoice
        if (!$this->isPastGrace()) return false;
        return PlatformInvoice::whereHas('items', fn($q) => $q->where('platform_subscription_id', $this->id))
            ->whereIn('status', ['Pending', 'Overdue', 'Partial'])
            ->exists();
    }
}
