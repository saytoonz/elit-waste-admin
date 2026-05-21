<?php

namespace App\Models\Platform;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlatformInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'currency',
        'subtotal',
        'tax',
        'discount',
        'total',
        'amount_paid',
        'status',
        'kind',
        'cycles_covered',
        'period_start',
        'period_end',
        'issued_at',
        'due_date',
        'paid_at',
        'paystack_reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'issued_at'    => 'date',
        'due_date'     => 'date',
        'paid_at'      => 'datetime',
        'subtotal'     => 'decimal:2',
        'tax'          => 'decimal:2',
        'discount'     => 'decimal:2',
        'total'        => 'decimal:2',
        'amount_paid'  => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(PlatformInvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(PlatformPayment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['Pending', 'Overdue', 'Partial']);
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->amount_paid);
    }

    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'Paid' => 'bg-green-50 text-green-700 ring-green-600/20',
            'Partial' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
            'Pending' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
            'Overdue' => 'bg-red-50 text-red-700 ring-red-600/20',
            'Cancelled' => 'bg-gray-50 text-gray-700 ring-gray-600/20',
            'Draft' => 'bg-gray-50 text-gray-700 ring-gray-600/20',
            default => 'bg-gray-50 text-gray-700 ring-gray-600/20',
        };
    }
}
