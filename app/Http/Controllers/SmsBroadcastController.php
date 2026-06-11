<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Platform\SmsBundle;
use App\Models\SmsBroadcast;
use App\Models\Zone;
use App\Services\AuditService;
use App\Services\SmsBroadcastService;
use Illuminate\Http\Request;

class SmsBroadcastController extends Controller
{
    public function __construct(protected SmsBroadcastService $broadcasts)
    {
    }

    public function index()
    {
        $broadcasts = SmsBroadcast::with('creator')->latest()->paginate(15);
        $bundle = SmsBundle::findActive();

        return view('sms_broadcasts.index', compact('broadcasts', 'bundle'));
    }

    public function create()
    {
        return view('sms_broadcasts.create', $this->formData() + ['broadcast' => null]);
    }

    public function edit(SmsBroadcast $sms_broadcast)
    {
        if (!$sms_broadcast->isEditable()) {
            return redirect()->route('sms_broadcasts.show', $sms_broadcast)
                ->with('error', 'Only draft, scheduled or failed broadcasts can be edited.');
        }

        return view('sms_broadcasts.create', $this->formData() + ['broadcast' => $sms_broadcast]);
    }

    /**
     * JSON endpoint used by the compose form: how many recipients match the
     * current filters, and how many credits the message would cost.
     */
    public function estimate(Request $request)
    {
        $data = $this->validateInput($request, requireMessage: false);
        [$recipients, , $creditsNeeded] = $this->broadcasts->prepare((string) ($data['message'] ?? ' '), $data);

        $bundle = SmsBundle::findActive();

        return response()->json([
            'recipients'     => $recipients->count(),
            'credits_needed' => $creditsNeeded,
            'credits_left'   => $bundle?->remaining,
            'sufficient'     => $bundle ? ($bundle->remaining >= $creditsNeeded) : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateInput($request, requireMessage: true);

        $broadcast = SmsBroadcast::create($this->broadcastAttributes($data) + ['created_by' => auth()->id()]);

        return $this->applyAction($request, $broadcast, $data);
    }

    public function update(Request $request, SmsBroadcast $sms_broadcast)
    {
        if (!$sms_broadcast->isEditable()) {
            return redirect()->route('sms_broadcasts.show', $sms_broadcast)
                ->with('error', 'This broadcast has already been sent and cannot be edited.');
        }

        $data = $this->validateInput($request, requireMessage: true);
        $sms_broadcast->update($this->broadcastAttributes($data));

        return $this->applyAction($request, $sms_broadcast, $data);
    }

    public function destroy(SmsBroadcast $sms_broadcast)
    {
        if (!$sms_broadcast->isEditable()) {
            return back()->with('error', 'Only draft, scheduled or failed broadcasts can be deleted.');
        }

        AuditService::log('Deleted SMS Broadcast', "#{$sms_broadcast->id} ({$sms_broadcast->status})");
        $sms_broadcast->delete();

        return redirect()->route('sms_broadcasts.index')->with('success', 'Broadcast deleted.');
    }

    /** Fire a Draft/Scheduled/Failed broadcast immediately. */
    public function sendNow(SmsBroadcast $sms_broadcast)
    {
        if (!$sms_broadcast->isEditable()) {
            return back()->with('error', 'This broadcast is already sending or sent.');
        }

        $error = $this->broadcasts->launch($sms_broadcast);
        if ($error) {
            return redirect()->route('sms_broadcasts.show', $sms_broadcast)->with('error', $error);
        }

        return redirect()->route('sms_broadcasts.show', $sms_broadcast)
            ->with('success', "Broadcast queued for {$sms_broadcast->recipients_count} customer(s).");
    }

    public function show(SmsBroadcast $sms_broadcast)
    {
        $sms_broadcast->load('creator');
        $recipients = $sms_broadcast->recipients()
            ->orderByRaw("case status when 'Failed' then 0 when 'Skipped' then 1 when 'Queued' then 2 else 3 end")
            ->orderBy('name')
            ->paginate(50);

        return view('sms_broadcasts.show', ['broadcast' => $sms_broadcast, 'recipients' => $recipients]);
    }

    /** Re-queue every Failed/Skipped recipient of this broadcast. */
    public function retryFailed(SmsBroadcast $sms_broadcast)
    {
        if (in_array($sms_broadcast->status, ['Draft', 'Scheduled'], true)) {
            return back()->with('error', 'This broadcast has not been sent yet.');
        }

        $count = $this->broadcasts->retryRecipients($sms_broadcast);
        return $count > 0
            ? back()->with('success', "Re-queued {$count} recipient(s).")
            : back()->with('error', 'No failed or skipped recipients to retry.');
    }

    /** Re-queue a single Failed/Skipped recipient. */
    public function retryRecipient(SmsBroadcast $sms_broadcast, \App\Models\SmsBroadcastRecipient $recipient)
    {
        if ($recipient->sms_broadcast_id !== $sms_broadcast->id) abort(404);
        if (!$recipient->isRetryable()) {
            return back()->with('error', 'Only failed or skipped recipients can be retried.');
        }

        $this->broadcasts->retryRecipients($sms_broadcast, $recipient);
        return back()->with('success', "Re-queued {$recipient->name}.");
    }

    // ─────────────────────────────────────────────────────────────────────────

    /** Handle the chosen submit action (send | draft | schedule) for a saved broadcast. */
    protected function applyAction(Request $request, SmsBroadcast $broadcast, array $data)
    {
        $action = $request->input('action', 'send');

        if ($action === 'draft') {
            $broadcast->update(['status' => 'Draft', 'scheduled_at' => null, 'failure_reason' => null]);
            AuditService::log('Saved SMS Broadcast Draft', "#{$broadcast->id}");
            return redirect()->route('sms_broadcasts.show', $broadcast)->with('success', 'Draft saved. Send or schedule it when ready.');
        }

        if ($action === 'schedule') {
            $broadcast->update([
                'status'         => 'Scheduled',
                'scheduled_at'   => $data['scheduled_at'],
                'failure_reason' => null,
            ]);
            AuditService::log('Scheduled SMS Broadcast', "#{$broadcast->id} for {$broadcast->scheduled_at->format('Y-m-d H:i')}");
            return redirect()->route('sms_broadcasts.show', $broadcast)
                ->with('success', 'Broadcast scheduled for ' . $broadcast->scheduled_at->format('M d, Y H:i') . '.');
        }

        // Send now
        $error = $this->broadcasts->launch($broadcast);
        if ($error) {
            return redirect()->route('sms_broadcasts.show', $broadcast)->with('error', $error);
        }

        return redirect()->route('sms_broadcasts.show', $broadcast)
            ->with('success', "Broadcast queued for {$broadcast->recipients_count} customer(s).");
    }

    protected function validateInput(Request $request, bool $requireMessage): array
    {
        return $request->validate([
            'message'          => ($requireMessage ? 'required' : 'nullable') . '|string|max:640',
            'audience'         => 'required|in:all,zones,customers',
            'zone_ids'         => 'required_if:audience,zones|array',
            'zone_ids.*'       => 'integer|exists:zones,id',
            'customer_ids'     => 'required_if:audience,customers|array',
            'customer_ids.*'   => 'integer|exists:customers,id',
            'types'            => 'nullable|array',
            'types.*'          => 'in:Residential,Commercial',
            'debtors_only'     => 'nullable|boolean',
            'include_inactive' => 'nullable|boolean',
            'action'           => 'nullable|in:send,draft,schedule',
            'scheduled_at'     => 'required_if:action,schedule|nullable|date|after:now',
        ], [
            'scheduled_at.required_if' => 'Pick a date and time for the scheduled delivery.',
            'scheduled_at.after'       => 'The scheduled time must be in the future.',
        ]);
    }

    /** Message + filter-snapshot attributes shared by store() and update(). */
    protected function broadcastAttributes(array $data): array
    {
        return [
            'message' => $data['message'],
            'filters' => $this->describeFilters($data),
            'status'  => 'Draft', // applyAction() promotes to Scheduled/Processing
        ];
    }

    protected function formData(): array
    {
        return [
            'zones'     => Zone::where('is_active', true)->orderBy('name')->get(),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'phone']),
            'bundle'    => SmsBundle::findActive(),
        ];
    }

    /** Snapshot of the filters (with zone names) stored on the broadcast for display. */
    protected function describeFilters(array $f): array
    {
        $snapshot = [
            'audience'         => $f['audience'],
            'types'            => $f['types'] ?? [],
            'debtors_only'     => (bool) ($f['debtors_only'] ?? false),
            'include_inactive' => (bool) ($f['include_inactive'] ?? false),
        ];
        if ($f['audience'] === 'zones') {
            $snapshot['zone_ids']   = $f['zone_ids'];
            $snapshot['zone_names'] = Zone::whereIn('id', $f['zone_ids'])->pluck('name')->all();
        }
        if ($f['audience'] === 'customers') {
            $snapshot['customer_ids'] = $f['customer_ids'];
        }
        return $snapshot;
    }
}
