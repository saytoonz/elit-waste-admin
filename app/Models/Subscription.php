<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'amount',
        'billing_cycle',
        'start_date',
        'due_date_offset_days',
        'next_billing_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'next_billing_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function servicePlan()
    {
        return $this->belongsTo(ServicePlan::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Stable identifier for the billing period that a given date falls in.
     * Used to make invoice generation idempotent (one invoice per period).
     *   Monthly   -> "2026-07"
     *   Weekly    -> "2026-W30" (ISO week)
     *   Quarterly -> "2026-Q3"
     */
    public function periodKey($date): string
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        return match ($this->billing_cycle) {
            'Weekly'    => $date->isoFormat('GGGG-[W]WW'),
            'Quarterly' => $date->format('Y') . '-Q' . $date->quarter,
            default     => $date->format('Y-m'), // Monthly (and any legacy value)
        };
    }

    /**
     * Advance a billing date by exactly one cycle, preserving the anchor day
     * without month overflow (e.g. Jan 31 -> Feb 28, not Mar 03).
     */
    public function addCycle(Carbon $date): Carbon
    {
        return match ($this->billing_cycle) {
            'Weekly'    => $date->copy()->addWeek(),
            'Quarterly' => $date->copy()->addQuarterNoOverflow(),
            default     => $date->copy()->addMonthNoOverflow(),
        };
    }

    /**
     * Next billing date strictly AFTER $pivot (default: today), stepping one
     * cycle at a time from $from. This prevents the "bill once per day until
     * caught up" cascade: a behind subscription is realigned to the future in
     * a single run.
     */
    public function nextDateAfter($from, $pivot = null): Carbon
    {
        $next  = $from instanceof Carbon ? $from->copy() : Carbon::parse($from);
        $pivot = $pivot ? ($pivot instanceof Carbon ? $pivot : Carbon::parse($pivot)) : Carbon::today();

        // Guard against an infinite loop on bad data.
        for ($i = 0; $i < 1000 && $next->lte($pivot); $i++) {
            $next = $this->addCycle($next);
        }

        return $next;
    }
}
