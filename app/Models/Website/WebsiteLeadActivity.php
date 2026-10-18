<?php

namespace App\Models\Website;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WebsiteLeadActivity extends Model
{
    public const TYPES = ['note' => 'Note', 'call' => 'Call', 'sms' => 'SMS', 'email' => 'Email', 'visit' => 'Site visit'];

    protected $fillable = ['website_lead_id', 'user_id', 'type', 'body'];

    public function lead()
    {
        return $this->belongsTo(WebsiteLead::class, 'website_lead_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
