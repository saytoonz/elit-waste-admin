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
     * The canonical billing anchor day: Monthly (and Quarterly/Yearly) are billed
     * on the 28th — chosen because the 28th exists in EVERY month including
     * February, in both leap and non-leap years. Weekly is billed on Saturday.
     *
     * Returns the first canonical billing date ON OR AFTER $date. Used when a
     * subscription is created or realigned so its schedule snaps onto the anchor.
     */
    public function anchorOnOrAfter($date): Carbon
    {
        $date = ($date instanceof Carbon ? $date->copy() : Carbon::parse($date))->startOfDay();

        if ($this->billing_cycle === 'Weekly') {
            return $date->isSaturday() ? $date->copy() : $date->copy()->next(Carbon::SATURDAY);
        }

        // Monthly / Quarterly / Yearly anchor on the 28th.
        $anchor = $date->copy()->day(28);

        return $anchor->gte($date)
            ? $anchor
            : $date->copy()->addMonthNoOverflow()->day(28);
    }

    /**
     * The next canonical billing date STRICTLY AFTER $pivot (default: today).
     * Used to advance the schedule after billing — it re-anchors to the 28th /
     * Saturday every time, so a subscription can never drift off the standard
     * schedule and a behind subscription is realigned to the future in one step
     * (no "bill once per day until caught up" cascade).
     */
    public function nextBillingDateAfter($pivot = null): Carbon
    {
        $pivot = $pivot === null
            ? Carbon::today()
            : ($pivot instanceof Carbon ? $pivot->copy() : Carbon::parse($pivot));
        $pivot = $pivot->startOfDay();

        if ($this->billing_cycle === 'Weekly') {
            return $pivot->copy()->next(Carbon::SATURDAY);
        }

        // Monthly / Quarterly / Yearly: step whole cycles on the 28th until
        // strictly after the pivot. The phase (which month) is preserved from
        // the current next_billing_date; the day is always re-anchored to 28.
        $step = match ($this->billing_cycle) {
            'Quarterly' => 3,
            'Yearly'    => 12,
            default     => 1, // Monthly (and any legacy value)
        };

        $date = ($this->next_billing_date ? Carbon::parse($this->next_billing_date) : $pivot)
            ->copy()->day(28);

        for ($i = 0; $i < 1200 && $date->lte($pivot); $i++) {
            $date = $date->addMonthsNoOverflow($step)->day(28);
        }

        return $date;
    }
}
