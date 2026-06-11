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

    /** One SMS credit covers up to this many characters; longer messages cost extra credits. */
    public const CHARS_PER_CREDIT = 160;

    /**
     * How many SMS credits a message costs: 1 credit per 160 characters (or part thereof).
     * 160 chars = 1 credit, 161 chars = 2 credits, 321 chars = 3 credits, ...
     */
    public static function creditsFor(string $message): int
    {
        return max(1, (int) ceil(mb_strlen($message) / self::CHARS_PER_CREDIT));
    }

    /**
     * Atomically consume $credits from the bundle. Returns true if the full amount
     * was reserved, false if the bundle is expired, inactive, or doesn't have
     * enough credits left (no partial consumption).
     *
     * Uses a SELECT ... FOR UPDATE row lock inside a transaction to avoid
     * concurrent overshoot when multiple SMS jobs race.
     */
    public function consume(int $credits = 1): bool
    {
        $credits = max(1, $credits);

        return DB::transaction(function () use ($credits) {
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
            if ($fresh->quantity_total - $fresh->quantity_used < $credits) {
                // Not enough left for this message — leave the remainder untouched
                return false;
            }

            $fresh->quantity_used += $credits;
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
