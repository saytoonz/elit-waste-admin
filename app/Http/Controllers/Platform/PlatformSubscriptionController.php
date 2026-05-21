<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\PlatformService;
use App\Models\Platform\PlatformSubscription;
use App\Services\AuditService;
use App\Services\PlatformBillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlatformSubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = PlatformSubscription::with('service');

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('force'))    $query->where('force_payment', $request->force === '1');
        if ($request->filled('service'))  $query->where('platform_service_id', $request->service);

        $subscriptions = $query->orderBy('next_billing_date')->paginate(30)->withQueryString();
        $services = PlatformService::active()->orderBy('name')->get();
        return view('platform.subscriptions.index', compact('subscriptions', 'services'));
    }

    public function create()
    {
        $services = PlatformService::active()->orderBy('name')->get();
        return view('platform.subscriptions.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $service = PlatformService::findOrFail($validated['platform_service_id']);

        $validated['unit_price']    = $validated['unit_price'] ?? $service->unit_price;
        $validated['currency']      = $service->currency;
        $validated['billing_cycle'] = $service->billing_cycle;
        $validated['grace_days']    = $validated['grace_days'] ?? $service->grace_days;
        $validated['next_billing_date'] = $validated['next_billing_date'] ?? $validated['start_date'];
        $validated['created_by']    = auth()->id();
        $validated['updated_by']    = auth()->id();

        $sub = PlatformSubscription::create($validated);
        AuditService::log('Created Platform Subscription', "{$service->name} qty={$sub->quantity}");
        return redirect()->route('platform.subscriptions.index')->with('success', 'Subscription created.');
    }

    public function edit(PlatformSubscription $subscription)
    {
        $services = PlatformService::active()->orderBy('name')->get();
        return view('platform.subscriptions.edit', compact('subscription', 'services'));
    }

    public function update(Request $request, PlatformSubscription $subscription)
    {
        $validated = $this->validatePayload($request);
        $validated['updated_by'] = auth()->id();
        $validated['force_payment'] = $request->boolean('force_payment');
        $validated['auto_renew']    = $request->boolean('auto_renew');
        $subscription->update($validated);
        AuditService::log('Updated Platform Subscription', "ID {$subscription->id}");
        return redirect()->route('platform.subscriptions.index')->with('success', 'Subscription updated.');
    }

    public function destroy(PlatformSubscription $subscription)
    {
        $subscription->update(['status' => 'Cancelled', 'auto_renew' => false]);
        AuditService::log('Cancelled Platform Subscription', "ID {$subscription->id}");
        return back()->with('success', 'Subscription cancelled.');
    }

    public function toggleForce(PlatformSubscription $subscription)
    {
        $subscription->update(['force_payment' => !$subscription->force_payment]);
        AuditService::log('Toggled Force Payment', "Sub {$subscription->id} → " . ($subscription->force_payment ? 'ON' : 'OFF'));
        return back()->with('success', 'Force-payment flag updated.');
    }

    public function reactivate(PlatformSubscription $subscription)
    {
        $subscription->update([
            'status' => 'Active',
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);
        AuditService::log('Reactivated Platform Subscription', "ID {$subscription->id}");
        return back()->with('success', 'Subscription reactivated.');
    }

    public function billNow(PlatformSubscription $subscription, PlatformBillingService $billing)
    {
        $invoice = $billing->generateCycleInvoice($subscription);
        $subscription->advanceBillingDate(1);
        $subscription->last_billed_date = Carbon::today()->toDateString();
        $subscription->save();
        AuditService::log('Manually Billed Subscription', "ID {$subscription->id} → Invoice #{$invoice->invoice_number}");
        return back()->with('success', "Generated invoice #{$invoice->invoice_number}");
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'platform_service_id' => 'required|exists:platform_services,id',
            'quantity'            => 'required|integer|min:1',
            'unit_price'          => 'nullable|numeric|min:0',
            'status'              => 'required|in:Active,Paused,Cancelled,Suspended',
            'start_date'          => 'required|date',
            'next_billing_date'   => 'nullable|date',
            'grace_days'          => 'nullable|integer|min:0|max:90',
            'force_payment'       => 'sometimes|boolean',
            'auto_renew'          => 'sometimes|boolean',
            'notes'               => 'nullable|string|max:2000',
        ]);
    }
}
