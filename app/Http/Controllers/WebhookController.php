<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Paystack Webhook. Verifies the signature against BOTH provider and
     * customer secrets so a single endpoint can receive events from either account.
     */
    public function handlePaystack(Request $request)
    {
        $signature = $request->header('x-paystack-signature');
        if (!$signature) {
            Log::warning('Paystack Webhook: Missing signature');
            return response()->json(['status' => 'error', 'message' => 'Missing signature'], 400);
        }

        $body = $request->getContent();
        $customerSecret = Setting::safeValue('paystack_secret_key') ?: config('services.paystack.secret');
        $providerSecret = config('services.paystack.provider_secret');

        $matchesCustomer = $customerSecret && hash_equals(hash_hmac('sha512', $body, $customerSecret), $signature);
        $matchesProvider = $providerSecret && hash_equals(hash_hmac('sha512', $body, $providerSecret), $signature);

        if (!$matchesCustomer && !$matchesProvider) {
            Log::warning('Paystack Webhook: Invalid Signature');
            return response()->json(['status' => 'error', 'message' => 'Invalid Signature'], 400);
        }

        $source = $matchesProvider ? 'provider' : 'customer';
        $event = $request->input('event');
        $data = $request->input('data');

        if ($event === 'charge.success') {
            $this->handleChargeSuccess($data, $source);
        }

        return response()->json(['status' => 'success'], 200);
    }

    protected function handleChargeSuccess($data, string $source = 'customer')
    {
        $reference = $data['reference'];
        $metadata = $data['metadata'] ?? [];
        // Settle at the pre-fee base; the gross charge includes the Paystack
        // processing fee the payer absorbed, which must not credit the invoice.
        $amount = isset($metadata['base_amount']) ? (float) $metadata['base_amount'] : $data['amount'] / 100;

        Log::info("Paystack Webhook ({$source}): charge.success ref={$reference}");

        // Provider account → platform billing (hosting/email/domain/SMS)
        if ($source === 'provider' || ($metadata['kind'] ?? null) === 'platform_invoice' || ($metadata['kind'] ?? null) === 'platform_invoice_bundle') {
            $this->handlePlatformChargeSuccess($data, $reference, $amount, $metadata);
            return;
        }

        // Customer account → waste-collection invoices
        $invoiceId = $metadata['invoice_id'] ?? null;
        if (Payment::where('reference', $reference)->exists()) return;

        Payment::create([
            'customer_id' => $metadata['customer_id'] ?? null,
            'invoice_id' => $invoiceId,
            'reference' => $reference,
            'amount' => $amount,
            'status' => 'Success',
            'channel' => 'Paystack',
            'paid_at' => now(),
            'metadata' => json_encode($data),
        ]);

        if ($invoiceId) {
            $invoice = Invoice::with('customer')->find($invoiceId);
            if ($invoice) {
                $newBalance = $invoice->balance_due - $amount;
                $invoice->update([
                    'balance_due' => max(0, $newBalance),
                    'status' => $newBalance <= 0 ? 'Paid' : 'Partial',
                ]);

                // Confirmation SMS — webhook is the primary success path for mobile money,
                // since Paystack doesn't reliably auto-redirect to the browser callback for MoMo.
                if ($invoice->customer && $invoice->customer->phone) {
                    $formattedAmount = number_format($amount, 2);
                    $msg = "Payment Received: GHS {$formattedAmount} for Invoice #{$invoice->invoice_number}. Thanks! - Elite Waste";
                    \App\Jobs\SendSmsJob::dispatch($invoice->customer->phone, $msg);
                    Log::info("Webhook: dispatched confirmation SMS to {$invoice->customer->phone} for #{$invoice->invoice_number}");
                }

                \App\Services\AuditService::log('Webhook Payment', "#{$invoice->invoice_number} GHS {$amount} ref={$reference}");
            }
        }
    }

    protected function handlePlatformChargeSuccess(array $data, string $reference, float $amount, array $metadata): void
    {
        if (\App\Models\Platform\PlatformPayment::where('reference', $reference)->exists()) return;

        $billing = app(\App\Services\PlatformBillingService::class);
        $bundledIds = $metadata['bundled_invoices'] ?? null;

        // If init-time conversion stashed the original amount, settle invoices in that
        // currency; otherwise fall back to the pre-fee base (already resolved into $amount).
        $originalAmount = isset($metadata['original_amount']) ? (float) $metadata['original_amount'] : $amount;

        if (is_array($bundledIds) && count($bundledIds) > 0) {
            $invoices = \App\Models\Platform\PlatformInvoice::whereIn('id', $bundledIds)->orderBy('issued_at')->get();
            $remaining = $originalAmount;
            $i = 0;
            foreach ($invoices as $inv) {
                $balance = (float) $inv->balance;
                if ($balance <= 0 || $remaining <= 0.0001) continue;
                $apply = min($balance, $remaining);
                $perRef = $i === 0 ? $reference : ($reference . '-' . $i);
                $billing->applyPayment($inv, $apply, $perRef, 'Paystack', $data + [
                    'bundle_total_original' => $originalAmount,
                    'bundle_total_charged'  => $amount,
                    'source' => 'webhook',
                ]);
                $remaining -= $apply;
                $i++;
            }
            return;
        }

        $invoiceId = $metadata['platform_invoice'] ?? null;
        if (!$invoiceId) return;
        $invoice = \App\Models\Platform\PlatformInvoice::find($invoiceId);
        if (!$invoice) return;
        $billing->applyPayment($invoice, $originalAmount, $reference, 'Paystack', $data + ['source' => 'webhook']);
    }
}
