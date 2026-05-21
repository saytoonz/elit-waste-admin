<?php

namespace App\Models\Platform;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingUserProvision extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_invoice_id',
        'platform_subscription_id',
        'name',
        'email',
        'password',
        'role',
        'status',
        'provisioned_user_id',
        'error_message',
        'requested_by',
    ];

    protected $casts = [
        'password' => 'encrypted',
    ];

    protected $hidden = ['password'];

    public function invoice()
    {
        return $this->belongsTo(PlatformInvoice::class, 'platform_invoice_id');
    }

    public function subscription()
    {
        return $this->belongsTo(PlatformSubscription::class, 'platform_subscription_id');
    }

    public function provisionedUser()
    {
        return $this->belongsTo(User::class, 'provisioned_user_id');
    }
}
