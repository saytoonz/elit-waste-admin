<?php

namespace App\Http\Controllers;

use App\Jobs\SendSmsJob;
use App\Models\Platform\SmsBundle;
use App\Models\SmsMessage;
use App\Services\AuditService;
use App\Services\MyCSMSService;
use Illuminate\Http\Request;

class SmsLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => 'nullable|in:' . implode(',', SmsMessage::STATUSES),
            'type'   => 'nullable|in:' . implode(',', array_keys(SmsMessage::TYPES)),
            'q'      => 'nullable|string|max:100',
            'from'   => 'nullable|date',
            'to'     => 'nullable|date',
        ]);

        $base = SmsMessage::query()
            ->when($filters['type'] ?? null, fn($q, $v) => $q->where('type', $v))
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($filters['q'] ?? null, function ($q, $v) {
                $q->where(fn($w) => $w->where('phone', 'like', "%{$v}%")
                    ->orWhere('gateway_message_id', $v)
                    ->orWhere('message', 'like', "%{$v}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$v}%")));
            });

        // Status counts reflect the other filters, so the chips double as a summary.
        $counts = (clone $base)->selectRaw('status, count(*) as n, sum(credits) as credits')
            ->groupBy('status')->get()->keyBy('status');

        $messages = (clone $base)
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->with('customer:id,name')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $bundle = SmsBundle::findActive();
        $gatewayBalance = app(MyCSMSService::class)->balance();

        return view('sms_logs.index', compact('messages', 'counts', 'filters', 'bundle', 'gatewayBalance'));
    }

    /** Re-send a failed or skipped SMS as a new log entry. */
    public function retry(SmsMessage $smsMessage)
    {
        if (! in_array($smsMessage->status, ['Failed', 'Skipped'], true)) {
            return back()->with('error', 'Only failed or skipped messages can be resent.');
        }
        if ($smsMessage->sms_broadcast_id) {
            return back()->with('error', 'Resend broadcast messages from the broadcast page.');
        }

        SendSmsJob::send($smsMessage->phone, $smsMessage->message, $smsMessage->type, $smsMessage->customer_id, $smsMessage->profile);
        AuditService::log('Resent SMS', "#{$smsMessage->id} to {$smsMessage->phone}");

        return back()->with('success', "SMS to {$smsMessage->phone} queued again.");
    }
}
