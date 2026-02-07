<?php

namespace App\Models;

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
}
