<?php

namespace App\Http\Controllers;

use App\Models\Platform\PlatformInvoice;
use App\Models\Platform\PlatformSubscription;
use App\Services\AuditService;
use App\Services\PaystackService;
use App\Services\PlatformBillingService;
use App\Support\PlatformConfig;
use Illuminate\Http\Request;

class MyBillingController extends Controller
{
    protected PaystackService $paystack;
    protected PlatformBillingService $billing;

    public function __construct(PlatformBillingService $billing)
    {
        // Platform billing always uses the provider (developer) Paystack account
        $this->paystack = PaystackService::forProvider();
        $this->billing  = $billing;
    }

    public function index(Request $request)
    {
        $query = PlatformInvoice::with('items.service');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Hide Cancelled from the default customer view — opt-in via status filter
            $query->where('status', '!=', 'Cancelled');
        }

        $invoices = $query->latest('issued_at')->paginate(20)->withQueryString();

        // Alias avoids collision with PlatformInvoice's `balance` accessor
        $unpaidByCurrency = PlatformInvoice::unpaid()
            ->selectRaw('currency, sum(total - amount_paid) as outstanding, count(*) as invoice_count')
            ->groupBy('currency')
            ->get();

        // For each currency, compute the GHS-equivalent the customer will actually be charged.
        $unpaidByCurrency = $unpaidByCurrency->map(function ($row) {
            try {
                $conv = $this->billing->convertForProviderCharge((float) $row->outstanding, $row->currency);
                $row->charge_currency = $conv['charge_currency'];
                $row->charge_amount   = $conv['charge_amount'];
                $row->fx_rate         = $conv['rate'];
                $row->needs_conversion = $row->currency !== $conv['charge_currency'];
            } catch (\Throwable $e) {
                $row->charge_currency = $row->currency;
                $row->charge_amount   = (float) $row->outstanding;
                $row->fx_rate         = 1.0;
                $row->needs_conversion = false;
                $row->conversion_error = $e->getMessage();
            }
            return $row;
        });

        return view('my.invoices.index', compact('invoices', 'unpaidByCurrency'));
    }

    public function show(PlatformInvoice $invoice)
    {
        $invoice->load(['items.service', 'items.subscription', 'payments']);

        // Compute the GHS equivalent for the Pay button when needed
        $conversion = null;
        if ($invoice->balance > 0) {
            try {
                $conversion = $this->billing->convertForProviderCharge((float) $invoice->balance, $invoice->currency);
            } catch (\Throwable $e) {
                $conversion = ['error' => $e->getMessage()];
            }
        }

        return view('my.invoices.show', compact('invoice', 'conversion'));
    }

    public function pay(Request $request, PlatformInvoice $invoice)
    {
        if (!PlatformConfig::paymentsEnabled()) {
            return back()->with('error', 'Payments are temporarily disabled by the provider.' . (PlatformConfig::maintenanceMessage() ? ' ' . PlatformConfig::maintenanceMessage() : ''));
        }
        if ($invoice->status === 'Paid') {
            return redirect()->route('my.invoices.show', $invoice)->with('error', 'Invoice is already paid.');
        }
        if ($invoice->status === 'Cancelled') {
            return redirect()->route('my.invoices.show', $invoice)->with('error', 'This invoice has been cancelled and cannot be paid.');
        }
        if ($invoice->balance <= 0) {
            return redirect()->route('my.invoices.show', $invoice)->with('error', 'Nothing left to pay.');
        }

        try {
            $conv = $this->billing->convertForProviderCharge((float) $invoice->balance, $invoice->currency);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $email = auth()->user()->email ?: 'billing@elitwaste.com';
        $callback = route('my.invoices.callback');
        $metadata = [
            'kind'              => 'platform_invoice',
            'platform_invoice'  => $invoice->id,
            'user_id'           => auth()->id(),
            'original_currency' => $conv['original_currency'],
            'original_amount'   => $conv['original_amount'],
            'fx_rate'           => $conv['rate'],
            'custom_fields'     => [
                ['display_name' => 'Invoice', 'variable_name' => 'invoice_number', 'value' => $invoice->invoice_number],
            ],
        ];

        $response = $this->paystack->initializeTransaction(
            $email,
            (float) $conv['charge_amount'],
            $callback,
            $metadata,
            $conv['charge_currency']
        );

        if (!empty($response['status']) && !empty($response['data']['authorization_url'])) {
            $invoice->update(['paystack_reference' => $response['data']['reference'] ?? null]);
            return redirect($response['data']['authorization_url']);
        }

        return back()->with('error', 'Unable to initialize payment: ' . ($response['message'] ?? 'Unknown error'));
    }

    public function payAll(Request $request)
    {
        if (!PlatformConfig::paymentsEnabled()) {
            return back()->with('error', 'Payments are temporarily disabled by the provider.' . (PlatformConfig::maintenanceMessage() ? ' ' . PlatformConfig::maintenanceMessage() : ''));
        }
        $currency = strtoupper((string) $request->input('currency', ''));
        if (!$currency) {
            return back()->with('error', 'Currency is required.');
        }

        $invoices = PlatformInvoice::unpaid()
            ->where('currency', $currency)
            ->orderBy('issued_at')
            ->get();

        if ($invoices->isEmpty()) {
            return back()->with('error', "No unpaid invoices in {$currency}.");
        }

        $totalBalance = (float) $invoices->sum(fn($i) => (float) $i->balance);
        if ($totalBalance <= 0) {
            return back()->with('error', 'Nothing to pay.');
        }

        try {
            $conv = $this->billing->convertForProviderCharge($totalBalance, $currency);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        $email = auth()->user()->email ?: 'billing@elitwaste.com';
        $callback = route('my.invoices.callback');
        $invoiceIds = $invoices->pluck('id')->all();
        $metadata = [
            'kind'              => 'platform_invoice_bundle',
            'bundled_invoices'  => $invoiceIds,
            'platform_invoice'  => $invoices->first()->id, // legacy fallback for single-invoice callback path
            'user_id'           => auth()->id(),
            'original_currency' => $conv['original_currency'],
            'original_amount'   => $conv['original_amount'],
            'fx_rate'           => $conv['rate'],
            'custom_fields'     => [
                ['display_name' => 'Invoice Count', 'variable_name' => 'count', 'value' => (string) count($invoiceIds)],
                ['display_name' => 'First Invoice', 'variable_name' => 'first', 'value' => $invoices->first()->invoice_number],
            ],
        ];

        $response = $this->paystack->initializeTransaction(
            $email,
            (float) $conv['charge_amount'],
            $callback,
            $metadata,
            $conv['charge_currency']
        );

        if (!empty($response['status']) && !empty($response['data']['authorization_url'])) {
            $ref = $response['data']['reference'] ?? null;
            if ($ref) {
                PlatformInvoice::whereIn('id', $invoiceIds)->update(['paystack_reference' => $ref]);
            }
            AuditService::log('Initiated Pay-All', "{$currency} {$totalBalance} ≈ {$conv['charge_currency']} {$conv['charge_amount']} across " . count($invoiceIds) . " invoices");
            return redirect($response['data']['authorization_url']);
        }

        return back()->with('error', 'Unable to initialize payment: ' . ($response['message'] ?? 'Unknown error'));
    }

    public function prepay(Request $request, PlatformSubscription $subscription)
    {
        if (!PlatformConfig::paymentsEnabled()) {
            return back()->with('error', 'Payments are temporarily disabled by the provider.');
        }
        $data = $request->validate([
            'cycles' => 'required|integer|min:1|max:24',
        ]);

        if (in_array($subscription->status, ['Cancelled'])) {
            return back()->with('error', 'Cannot prepay a cancelled subscription.');
        }

        $invoice = $this->billing->generatePrepayInvoice($subscription, (int) $data['cycles']);
        AuditService::log('Prepay Invoice Created', "Sub {$subscription->id} × {$data['cycles']} → #{$invoice->invoice_number}");
        return redirect()->route('my.invoices.show', $invoice)->with('success', "Prepay invoice generated for {$data['cycles']} cycle(s). Pay it to extend your subscription.");
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        if (!$reference) {
            return redirect()->route('my.invoices.index')->with('error', 'No payment reference provided.');
        }

        $response = $this->paystack->verifyTransaction($reference);
        if (!($response['status'] ?? false) || ($response['data']['status'] ?? null) !== 'success') {
            AuditService::log('Platform Payment Verify Failed', "Ref {$reference}");
            return redirect()->route('my.invoices.index')->with('error', 'Payment verification failed.');
        }

        $data = $response['data'];
        $chargedAmount = $data['amount'] / 100; // Amount actually charged (GHS if converted)
        $metadata = $data['metadata'] ?? [];
        $bundledIds = $metadata['bundled_invoices'] ?? null;
        $kind = $metadata['kind'] ?? 'platform_invoice';

        // When we converted USD→GHS at init time, we stashed the original amount.
        // Use it to settle the invoice in its native currency. Fall back to the
        // pre-fee base — never the gross, which includes the Paystack fee.
        $originalAmount = isset($metadata['original_amount'])
            ? (float) $metadata['original_amount']
            : (float) ($metadata['base_amount'] ?? $chargedAmount);

        // Idempotency: if any payment row with this reference already exists, just show success
        if (\App\Models\Platform\PlatformPayment::where('reference', $reference)->exists()) {
            $firstId = is_array($bundledIds) ? ($bundledIds[0] ?? null) : ($metadata['platform_invoice'] ?? null);
            return $firstId
                ? redirect()->route('my.invoices.show', $firstId)->with('success', 'Payment already verified.')
                : redirect()->route('my.invoices.index')->with('success', 'Payment already verified.');
        }

        // Bundled Pay-All path
        if ($kind === 'platform_invoice_bundle' && is_array($bundledIds) && count($bundledIds) > 0) {
            $invoices = PlatformInvoice::whereIn('id', $bundledIds)->orderBy('issued_at')->get();
            // Spread payment across invoices in order using ORIGINAL-currency amounts
            $remaining = (float) $originalAmount;
            $localRef = $reference;
            $i = 0;
            foreach ($invoices as $inv) {
                $balance = (float) $inv->balance;
                if ($balance <= 0 || $remaining <= 0.0001) continue;
                $apply = min($balance, $remaining);
                $perRef = $i === 0 ? $localRef : ($localRef . '-' . $i);
                $this->billing->applyPayment($inv, $apply, $perRef, 'Paystack', $data + [
                    'bundle_total_original' => $originalAmount,
                    'bundle_total_charged'  => $chargedAmount,
                ]);
                $remaining -= $apply;
                $i++;
            }
            AuditService::log('Bundled Payment Success', "Ref {$reference} {$originalAmount} (charged {$chargedAmount} {$data['currency']}) across " . count($bundledIds) . " invoices");
            return redirect()->route('my.invoices.index')->with('success', 'Payment successful. All invoices have been settled. Thank you!');
        }

        // Single-invoice path
        $invoiceId = $metadata['platform_invoice'] ?? null;
        $invoice = $invoiceId ? PlatformInvoice::find($invoiceId) : null;
        if (!$invoice) {
            return redirect()->route('my.invoices.index')->with('error', 'Invoice not found.');
        }

        $this->billing->applyPayment($invoice, (float) $originalAmount, $reference, 'Paystack', $data);
        AuditService::log('Platform Payment Success', "#{$invoice->invoice_number} {$invoice->currency} {$originalAmount} (charged {$chargedAmount} {$data['currency']})");

        return redirect()->route('my.invoices.show', $invoice)->with('success', 'Payment successful. Thank you!');
    }
}
