<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_category_id',
        'period',
        'year',
        'month',
        'quarter',
        'amount',
        'alert_enabled',
        'alert_threshold_percent',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount'                  => 'decimal:2',
        'alert_enabled'           => 'boolean',
        'year'                    => 'integer',
        'month'                   => 'integer',
        'quarter'                 => 'integer',
        'alert_threshold_percent' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPeriodLabelAttribute(): string
    {
        if ($this->period === 'Monthly' && $this->month) {
            return \Carbon\Carbon::create($this->year, $this->month, 1)->format('M Y');
        }
        if ($this->period === 'Quarterly' && $this->quarter) {
            return "Q{$this->quarter} {$this->year}";
        }
        return (string) $this->year;
    }

    public function getSpentAttribute(): float
    {
        $query = Expense::where('expense_category_id', $this->expense_category_id)
            ->approvedOrPaid();

        if ($this->period === 'Monthly' && $this->month) {
            $query->whereYear('expense_date', $this->year)
                  ->whereMonth('expense_date', $this->month);
        } elseif ($this->period === 'Quarterly' && $this->quarter) {
            $startMonth = ($this->quarter - 1) * 3 + 1;
            $start = \Carbon\Carbon::create($this->year, $startMonth, 1)->startOfMonth();
            $end = $start->copy()->addMonths(2)->endOfMonth();
            $query->whereBetween('expense_date', [$start, $end]);
        } else {
            $query->whereYear('expense_date', $this->year);
        }

        return (float) $query->sum('total_amount');
    }

    public function getRemainingAttribute(): float
    {
        return max(0, (float) $this->amount - $this->spent);
    }

    public function getUtilizationPercentAttribute(): float
    {
        if ($this->amount <= 0) return 0;
        return round(($this->spent / $this->amount) * 100, 1);
    }
}
