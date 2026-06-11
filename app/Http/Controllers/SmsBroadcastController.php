<?php

namespace App\Http\Controllers;

use App\Jobs\SendSmsJob;
use App\Models\Customer;
use App\Models\Platform\PlatformSubscription;
use App\Models\Platform\SmsBundle;
use App\Models\SmsBroadcast;
use App\Models\Zone;
use App\Services\AuditService;
use Illuminate\Http\Request;

class SmsBroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = SmsBroadcast::with('creator')->latest()->paginate(15);
        $bundle = SmsBundle::findActive();

        return view('sms_broadcasts.index', compact('broadcasts', 'bundle'));
    }

    public function create()
    {
        $zones = Zone::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'phone']);
        $bundle = SmsBundle::findActive();

        return view('sms_broadcasts.create', compact('zones', 'customers', 'bundle'));
    }

    /**
     * JSON endpoint used by the compose form: how many recipients match the
     * current filters, and how many credits the message would cost.
     */
    public function estimate(Request $request)
    {
        $data = $this->validateFilters($request, requireMessage: false);
        $recipients = $this->resolveRecipients($data);

        $message = (string) $request->input('message', '');
        $creditsNeeded = 0;
        foreach ($recipients as $r) {
            $creditsNeeded += SmsBundle::creditsFor($this->personalize($message ?: ' ', $r));
        }

        $bundle = SmsBundle::findActive();

        return response()->json([
            'recipients'      => $recipients->count(),
            'credits_needed'  => $creditsNeeded,
            'credits_left'    => $bundle?->remaining,
            'sufficient'      => $bundle ? ($bundle->remaining >= $creditsNeeded) : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateFilters($request, requireMessage: true);

        $recipients = $this->resolveRecipients($data);
        if ($recipients->isEmpty()) {
            return back()->withInput()->with('error', 'No customers with a phone number match these filters.');
        }

        // Render each personalized message up-front so the credit check is exact.
        $messages = $recipients->map(fn($c) => [
            'phone'   => $c->phone,
            'body'    => $this->personalize($data['message'], $c),
        ]);
        $creditsNeeded = $messages->sum(fn($m) => SmsBundle::creditsFor($m['body']));

        // Quota gate: with an active bundle, the whole broadcast must fit.
        // With an SMS subscription but no active bundle, every send would be
        // blocked anyway — fail fast with a useful message.
        $bundle = SmsBundle::findActive();
        if ($bundle) {
            if ($bundle->remaining < $creditsNeeded) {
                return back()->withInput()->with('error',
                    "Not enough SMS credits: this broadcast needs {$creditsNeeded} but only {$bundle->remaining} remain. Top up your bundle or narrow the audience.");
            }
        } else {
            $hasSmsSubscription = PlatformSubscription::query()
                ->whereHas('service', fn($q) => $q->where('type', 'SMS'))
                ->whereIn('status', ['Active', 'Paused', 'Suspended'])
                ->exists();
            if ($hasSmsSubscription) {
                return back()->withInput()->with('error', 'No active SMS bundle — pay your SMS invoice to restore sending.');
            }
        }

        $broadcast = SmsBroadcast::create([
            'message'           => $data['message'],
            'filters'           => $this->describeFilters($data),
            'recipients_count'  => $recipients->count(),
            'credits_estimated' => $creditsNeeded,
            'status'            => 'Processing',
            'created_by'        => auth()->id(),
        ]);

        foreach ($messages as $m) {
            SendSmsJob::dispatch($m['phone'], $m['body'], SendSmsJob::PROFILE_CUSTOMER, $broadcast->id);
        }

        AuditService::log('Sent SMS Broadcast', "#{$broadcast->id} to {$recipients->count()} customer(s), ~{$creditsNeeded} credit(s)");

        return redirect()->route('sms_broadcasts.show', $broadcast)
            ->with('success', "Broadcast queued for {$recipients->count()} customer(s).");
    }

    public function show(SmsBroadcast $sms_broadcast)
    {
        $sms_broadcast->load('creator');
        return view('sms_broadcasts.show', ['broadcast' => $sms_broadcast]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    protected function validateFilters(Request $request, bool $requireMessage): array
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
        ]);
    }

    /**
     * Resolve the audience to customers that have a phone number, deduplicated
     * by phone. Each customer carries an `outstanding` sum for {balance}.
     */
    protected function resolveRecipients(array $f)
    {
        $query = Customer::query()
            ->withSum(['invoices as outstanding' => fn($q) => $q->whereNotIn('status', ['Paid', 'Cancelled'])], 'balance_due')
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        if (empty($f['include_inactive'])) {
            $query->where('is_active', true);
        }

        if (($f['audience'] ?? 'all') === 'zones') {
            $query->whereIn('zone_id', $f['zone_ids'] ?? []);
        } elseif (($f['audience'] ?? 'all') === 'customers') {
            $query->whereIn('id', $f['customer_ids'] ?? []);
        }

        if (!empty($f['types'])) {
            $query->whereIn('type', $f['types']);
        }

        $customers = $query->orderBy('name')->get();

        if (!empty($f['debtors_only'])) {
            $customers = $customers->filter(fn($c) => (float) ($c->outstanding ?? 0) > 0);
        }

        return $customers->unique(fn($c) => preg_replace('/\s+/', '', $c->phone))->values();
    }

    /** Replace {name} and {balance} placeholders with the customer's values. */
    protected function personalize(string $message, Customer $customer): string
    {
        return str_replace(
            ['{name}', '{balance}'],
            [$customer->name, 'GHS ' . number_format((float) ($customer->outstanding ?? 0), 2)],
            $message
        );
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
