<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Paystack Webhook
     */
    public function handlePaystack(Request $request)
    {
        // 1. Verify Signature
        $secret = config('services.paystack.secret');
        $signature = $request->header('x-paystack-signature');

        if (!$signature || $signature !== hash_hmac('sha512', $request->getContent(), $secret)) {
            Log::warning('Paystack Webhook: Invalid Signature');
            return response()->json(['status' => 'error', 'message' => 'Invalid Signature'], 400);
        }

        // 2. Process Event
        $event = $request->input('event');
        $data = $request->input('data');

        if ($event === 'charge.success') {
            $this->handleChargeSuccess($data);
        }

        return response()->json(['status' => 'success'], 200);
    }

    protected function handleChargeSuccess($data)
    {
        $reference = $data['reference'];
        $amount = $data['amount'] / 100; // kobo to GHS
        $metadata = $data['metadata'] ?? [];
        $invoiceId = $metadata['invoice_id'] ?? null;

        Log::info("Paystack Webhook: Payment success for reference {$reference}");

        // Check if already recorded
        if (Payment::where('reference', $reference)->exists()) {
            return;
        }

        // Record Payment
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

        // Update Invoice
        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                $newBalance = $invoice->balance_due - $amount;
                $invoice->update([
                    'balance_due' => max(0, $newBalance),
                    'status' => $newBalance <= 0 ? 'Paid' : 'Partial'
                ]);
            }
        }
    }
}
