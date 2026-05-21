<?php

namespace App\Http\Controllers;

use App\Models\Platform\PendingUserProvision;
use App\Models\Platform\PlatformInvoice;
use App\Models\Platform\PlatformInvoiceItem;
use App\Models\Platform\PlatformService;
use App\Models\Platform\PlatformSubscription;
use App\Models\User;
use App\Services\AuditService;
use App\Support\PlatformConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MyServicesController extends Controller
{
    /** Roles a customer is allowed to assign when provisioning a new email user. */
    private const ASSIGNABLE_PROVISIONING_ROLES = ['Collector', 'Supervisor', 'Accountant', 'Admin'];

    public function index()
    {
        $subscriptions = PlatformSubscription::with('service')
            ->whereIn('status', ['Active', 'Suspended', 'Paused'])
            ->orderBy('platform_service_id')
            ->get();

        // Totals grouped by currency
        $totals = $subscriptions->groupBy('currency')->map(function ($subs) {
            return [
                'monthly' => $subs->sum(fn($s) => $s->monthly_equivalent),
                'yearly'  => $subs->sum(fn($s) => $s->yearly_equivalent),
            ];
        });

        $unpaidInvoices = PlatformInvoice::unpaid()
            ->whereHas('items', fn($q) => $q->whereNotNull('platform_subscription_id'))
            ->orderBy('due_date')
            ->take(5)
            ->get();

        $emailService  = PlatformService::active()->where('type', 'Email')->orderBy('sort_order')->first();
        $otherAddable  = PlatformService::active()->where('customer_addable', true)->where('type', '!=', 'Email')->orderBy('sort_order')->get();
        $managedServices = PlatformService::active()->where('customer_addable', false)->orderBy('sort_order')->get();

        return view('my.services.index', compact(
            'subscriptions', 'totals', 'unpaidInvoices',
            'emailService', 'otherAddable', 'managedServices'
        ));
    }

    public function adjustQuantity(Request $request, PlatformSubscription $subscription)
    {
        $service = $subscription->service;
        if (!$service) abort(404);
        if (!$service->is_quantity_based || !$service->customer_addable) {
            return back()->with('error', 'This service cannot be adjusted from the portal.');
        }

        $data = $request->validate([
            'quantity' => "required|integer|min:{$service->min_quantity}|max:9999",
        ]);

        $old = $subscription->quantity;
        $subscription->update(['quantity' => $data['quantity'], 'updated_by' => auth()->id()]);
        AuditService::log('Adjusted My Service Quantity', $service->name . " {$old}→{$data['quantity']}");
        return back()->with('success', "Updated to {$data['quantity']} units. Will apply at next billing cycle.");
    }

    public function subscribe(Request $request)
    {
        if (!PlatformConfig::paymentsEnabled()) {
            return back()->with('error', 'New subscriptions are temporarily disabled by the provider.');
        }

        $data = $request->validate([
            'platform_service_id' => 'required|exists:platform_services,id',
            'quantity'            => 'nullable|integer|min:1',
        ]);

        $service = PlatformService::findOrFail($data['platform_service_id']);
        if (!$service->is_active || !$service->customer_addable) {
            return back()->with('error', 'This service is not available for self-subscription.');
        }

        // Email purchases route through the dedicated provisioning form
        if ($service->type === 'Email') {
            return redirect()->route('my.services.email.form');
        }

        $qty = max($service->min_quantity, (int) ($data['quantity'] ?? $service->default_quantity));

        // If service is quantity-based and a subscription already exists, bump the qty instead of creating duplicate
        if ($service->is_quantity_based) {
            $existing = PlatformSubscription::where('platform_service_id', $service->id)
                ->whereIn('status', ['Active', 'Paused', 'Suspended'])
                ->first();
            if ($existing) {
                $existing->update(['quantity' => $existing->quantity + $qty, 'updated_by' => auth()->id()]);
                AuditService::log('Increased Subscription Quantity', "{$service->name} +{$qty}");
                return redirect()->route('my.services.index')->with('success', "Added {$qty} more to {$service->name}.");
            }
        }

        $sub = PlatformSubscription::create([
            'platform_service_id' => $service->id,
            'quantity'            => $qty,
            'unit_price'          => $service->unit_price,
            'currency'            => $service->currency,
            'billing_cycle'       => $service->billing_cycle,
            'status'              => 'Active',
            'start_date'          => now()->toDateString(),
            'next_billing_date'   => now()->toDateString(),
            'auto_renew'          => true,
            'force_payment'       => false,
            'grace_days'          => $service->grace_days,
            'created_by'          => auth()->id(),
            'updated_by'          => auth()->id(),
        ]);

        AuditService::log('Subscribed to Platform Service', "{$service->name} qty={$qty}");
        return redirect()->route('my.services.index')->with('success', "Subscribed to {$service->name}.");
    }

    /**
     * Show the form for buying a new Email account. Customer enters name/email/password/role;
     * after payment, the user is auto-created and the Email subscription quantity is bumped.
     */
    public function emailForm()
    {
        $service = PlatformService::active()->where('type', 'Email')->first();
        if (!$service) {
            return redirect()->route('my.services.index')->with('error', 'Email service is not available.');
        }
        $roles = self::ASSIGNABLE_PROVISIONING_ROLES;
        $emailDomain = $this->lockedEmailDomain();
        return view('my.services.add-email', compact('service', 'roles', 'emailDomain'));
    }

    public function emailPurchase(Request $request)
    {
        if (!PlatformConfig::paymentsEnabled()) {
            return back()->with('error', 'Payments are temporarily disabled by the provider. Please try again later.');
        }

        $service = PlatformService::active()->where('type', 'Email')->first();
        if (!$service) {
            return redirect()->route('my.services.index')->with('error', 'Email service is not available.');
        }

        $domain = $this->lockedEmailDomain();

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email_local' => [
                'required',
                'string',
                'max:64',
                // RFC-friendly local-part: letters, digits, dot, underscore, plus, hyphen. No '@'.
                'regex:/^[A-Za-z0-9](?:[A-Za-z0-9._+\-]*[A-Za-z0-9])?$/',
            ],
            'password' => 'required|string|min:8|confirmed',
            'role'     => ['required', Rule::in(self::ASSIGNABLE_PROVISIONING_ROLES)],
        ], [
            'email_local.regex' => 'Use only letters, digits, dot, underscore, plus or hyphen — and no @.',
        ]);

        $fullEmail = strtolower($data['email_local']) . '@' . $domain;

        // Uniqueness checks on the constructed address
        $userExists = \App\Models\User::where('email', $fullEmail)->exists();
        $pendingExists = PendingUserProvision::where('email', $fullEmail)->where('status', 'Pending')->exists();
        if ($userExists || $pendingExists) {
            return back()
                ->withInput()
                ->withErrors(['email_local' => 'This email address is already taken.']);
        }

        // Stash the constructed email into the same shape the rest of the flow expects
        $data['email'] = $fullEmail;

        return DB::transaction(function () use ($data, $service) {
            // Locate or stub a subscription so we can attribute the invoice to it.
            // We do NOT bump quantity here — quantity is bumped when payment succeeds.
            $subscription = PlatformSubscription::where('platform_service_id', $service->id)
                ->whereIn('status', ['Active', 'Paused', 'Suspended'])
                ->first();

            if (!$subscription) {
                $subscription = PlatformSubscription::create([
                    'platform_service_id' => $service->id,
                    'quantity'            => 0,
                    'unit_price'          => $service->unit_price,
                    'currency'            => $service->currency,
                    'billing_cycle'       => $service->billing_cycle,
                    'status'              => 'Active',
                    'start_date'          => now()->toDateString(),
                    'next_billing_date'   => now()->addMonth()->toDateString(),
                    'auto_renew'          => true,
                    'force_payment'       => false,
                    'grace_days'          => $service->grace_days,
                    'created_by'          => auth()->id(),
                    'updated_by'          => auth()->id(),
                ]);
            }

            $lineTotal = $service->unit_price; // one seat × one cycle

            $invoice = PlatformInvoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'currency'       => $service->currency,
                'subtotal'       => $lineTotal,
                'tax'            => 0,
                'discount'       => 0,
                'total'          => $lineTotal,
                'amount_paid'    => 0,
                'status'         => 'Pending',
                'kind'           => 'Manual',
                'cycles_covered' => 1,
                'period_start'   => now()->toDateString(),
                'period_end'     => now()->addMonth()->subDay()->toDateString(),
                'issued_at'      => now()->toDateString(),
                'due_date'       => now()->addDays($service->grace_days)->toDateString(),
                'created_by'     => auth()->id(),
                'notes'          => "Email account provisioning: {$data['email']}",
            ]);

            PlatformInvoiceItem::create([
                'platform_invoice_id'      => $invoice->id,
                'platform_subscription_id' => $subscription->id,
                'platform_service_id'      => $service->id,
                'description'              => "New email account: {$data['email']} ({$service->billing_cycle})",
                'quantity'                 => 1,
                'unit_price'               => $service->unit_price,
                'line_total'               => $lineTotal,
            ]);

            PendingUserProvision::create([
                'platform_invoice_id'      => $invoice->id,
                'platform_subscription_id' => $subscription->id,
                'name'     => $data['name'],
                'email'    => strtolower($data['email']),
                'password' => $data['password'],
                'role'     => $data['role'],
                'status'   => 'Pending',
                'requested_by' => auth()->id(),
            ]);

            AuditService::log('Requested Email Account', "{$data['email']} → invoice #{$invoice->invoice_number}");

            return redirect()->route('my.invoices.pay', $invoice)
                ->with('success', 'Order placed. Complete payment to provision the account.');
        });
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $n = 'PLT-' . date('Ym') . '-' . strtoupper(Str::random(5));
        } while (PlatformInvoice::where('invoice_number', $n)->exists());
        return $n;
    }

    /**
     * The email domain that all customer-provisioned accounts are locked to.
     */
    private function lockedEmailDomain(): string
    {
        return strtolower((string) config('platform.email_domain', 'elitewasteghana.com'));
    }
}
