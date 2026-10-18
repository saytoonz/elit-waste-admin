<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;

class WebsiteSubscriber extends Model
{
    protected $fillable = ['email', 'name', 'is_active', 'ip'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
