<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'expense_category_id',
        'vendor_id',
        'zone_id',
        'amount',
        'tax_amount',
        'frequency',
        'start_date',
        'end_date',
        'next_run_date',
        'last_run_date',
        'payment_method',
        'auto_approve',
        'is_active',
        'description',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_run_date' => 'date',
        'last_run_date' => 'date',
        'auto_approve' => 'boolean',
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function advanceNextRunDate(): void
    {
        $base = Carbon::parse($this->next_run_date);
        $this->next_run_date = match ($this->frequency) {
            'Daily' => $base->copy()->addDay(),
            'Weekly' => $base->copy()->addWeek(),
            'Monthly' => $base->copy()->addMonth(),
            'Quarterly' => $base->copy()->addMonths(3),
            'Yearly' => $base->copy()->addYear(),
            default => $base->copy()->addMonth(),
        };
    }

    public function isDue(): bool
    {
        if (!$this->is_active) return false;
        if ($this->end_date && Carbon::parse($this->end_date)->isPast()) return false;
        return Carbon::parse($this->next_run_date)->isToday() || Carbon::parse($this->next_run_date)->isPast();
    }
}
