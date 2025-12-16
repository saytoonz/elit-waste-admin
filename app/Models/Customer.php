<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'secondary_phone',
        'address',
        'landmark',
        'zone_id',
        'gps_coordinates',
        'type',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getBalanceAttribute()
    {
        return $this->invoices()->where('status', '!=', 'Paid')->where('status', '!=', 'Cancelled')->sum('balance_due');
    }
}
