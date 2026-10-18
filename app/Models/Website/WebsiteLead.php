<?php

namespace App\Models\Website;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WebsiteLead extends Model
{
    public const STATUSES = ['New', 'Contacted', 'Qualified', 'Won', 'Lost'];

    protected $fillable = [
        'type',
        'status',
        'name',
        'email',
        'phone',
        'company',
        'location',
        'service_interest',
        'frequency',
        'message',
        'source_url',
        'assigned_to',
        'customer_id',
        'follow_up_at',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'follow_up_at' => 'date',
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function activities()
    {
        return $this->hasMany(WebsiteLeadActivity::class)->latest();
    }

    public function getTypeLabelAttribute(): string
    {
        return config("website.form_types.{$this->type}.label", ucfirst($this->type));
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'New'       => 'bg-blue-50 text-blue-700 ring-blue-600/20',
            'Contacted' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
            'Qualified' => 'bg-purple-50 text-purple-700 ring-purple-600/20',
            'Won'       => 'bg-green-50 text-green-700 ring-green-600/20',
            'Lost'      => 'bg-gray-50 text-gray-600 ring-gray-500/20',
            default     => 'bg-gray-50 text-gray-600 ring-gray-500/20',
        };
    }
}
