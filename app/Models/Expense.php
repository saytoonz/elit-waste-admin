<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_number',
        'expense_category_id',
        'vendor_id',
        'zone_id',
        'recurring_expense_id',
        'expense_date',
        'amount',
        'tax_amount',
        'total_amount',
        'payment_method',
        'reference',
        'attachment_path',
        'attachment_name',
        'status',
        'description',
        'rejection_reason',
        'notes',
        'recorded_by',
        'approved_by',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
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

    public function recurringExpense()
    {
        return $this->belongsTo(RecurringExpense::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeBetween($query, $start, $end)
    {
        return $query->whereBetween('expense_date', [$start, $end]);
    }

    public function scopeApprovedOrPaid($query)
    {
        return $query->whereIn('status', ['Approved', 'Paid']);
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'Paid'      => 'bg-green-50 text-green-700 ring-green-600/20',
            'Approved'  => 'bg-blue-50 text-blue-700 ring-blue-600/20',
            'Pending'   => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
            'Rejected'  => 'bg-red-50 text-red-700 ring-red-600/20',
            'Cancelled' => 'bg-gray-50 text-gray-700 ring-gray-600/20',
            'Draft'     => 'bg-gray-50 text-gray-700 ring-gray-600/20',
            default     => 'bg-gray-50 text-gray-700 ring-gray-600/20',
        };
    }
}
