<?php

namespace App\Http\Controllers;

use App\Models\Platform\PlatformService;
use App\Models\Platform\PlatformSubscription;
use App\Models\Platform\SmsBundle;
use Illuminate\Http\Request;

class MySmsBundleController extends Controller
{
    public function index(Request $request)
    {
        $active = SmsBundle::active()->orderBy('period_end')->get();
        $history = SmsBundle::where(function ($q) {
                $q->where('status', '!=', 'Active')
                  ->orWhereDate('period_end', '<', now());
            })
            ->orderByDesc('period_end')
            ->limit(24)
            ->get();

        $smsService = PlatformService::active()->where('type', 'SMS')->first();
        $smsSubscription = PlatformSubscription::query()
            ->whereHas('service', fn($q) => $q->where('type', 'SMS'))
            ->whereIn('status', ['Active', 'Paused', 'Suspended'])
            ->with('service')
            ->first();

        return view('my.sms.index', compact('active', 'history', 'smsService', 'smsSubscription'));
    }
}
