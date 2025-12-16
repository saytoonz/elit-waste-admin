<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'amount',
        'balance_due',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Helpers
    public function getIsPaidAttribute()
    {
        return $this->status === 'Paid';
    }

    public function getIsOverdueAttribute()
    {
        return $this->status === 'Overdue'; // Or check date
    }
}
