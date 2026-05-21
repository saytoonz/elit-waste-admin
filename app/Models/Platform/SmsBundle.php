<?php

namespace App\Models\Platform;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SmsBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_subscription_id',
        'platform_invoice_id',
        'quantity_total',
        'quantity_used',
        'period_start',
        'period_end',
        'status',
        'notes',
    ];

    protected $casts = [
        'period_start'   => 'date',
        'period_end'     => 'date',
        'quantity_total' => 'integer',
        'quantity_used'  => 'integer',
    ];

    public function subscription()
    {
        return $this->belongsTo(PlatformSubscription::class, 'platform_subscription_id');
    }

    public function invoice()
    {
        return $this->belongsTo(PlatformInvoice::class, 'platform_invoice_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active')
                     ->whereDate('period_end', '>=', Carbon::today());
    }

    public function getRemainingAttribute(): int
    {
        return max(0, $this->quantity_total - $this->quantity_used);
    }

    public function getUsagePercentAttribute(): float
    {
        if ($this->quantity_total <= 0) return 0;
        return round(($this->quantity_used / $this->quantity_total) * 100, 1);
    }

    public function getDaysRemainingAttribute(): int
    {
        return max(0, Carbon::today()->diffInDays(Carbon::parse($this->period_end), false));
    }

    public function isActive(): bool
    {
        if ($this->status !== 'Active') return false;
        if (Carbon::today()->greaterThan(Carbon::parse($this->period_end))) return false;
        return $this->remaining > 0;
    }

    /**
     * Atomically consume one message from the bundle. Returns true if a slot was
     * reserved, false if the bundle is exhausted or expired.
     *
     * Uses a SELECT ... FOR UPDATE row lock inside a transaction to avoid
     * concurrent overshoot when multiple SMS jobs race.
     */
    public function consumeOne(): bool
    {
        return DB::transaction(function () {
            $fresh = static::where('id', $this->id)->lockForUpdate()->first();
            if (!$fresh) return false;

            if ($fresh->status !== 'Active') return false;
            if (Carbon::today()->greaterThan(Carbon::parse($fresh->period_end))) {
                $fresh->update(['status' => 'Expired']);
                return false;
            }
            if ($fresh->quantity_used >= $fresh->quantity_total) {
                $fresh->update(['status' => 'Exhausted']);
                return false;
            }

            $fresh->quantity_used += 1;
            if ($fresh->quantity_used >= $fresh->quantity_total) {
                $fresh->status = 'Exhausted';
            }
            $fresh->save();

            $this->refresh();
            return true;
        });
    }

    /**
     * Find the currently-active SMS bundle (if any).
     * Single-tenant install: there's only one customer, so we just look for the freshest active bundle.
     */
    public static function findActive(): ?self
    {
        return static::active()
            ->orderBy('period_end')
            ->first();
    }
}
