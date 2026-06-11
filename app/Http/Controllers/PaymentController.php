<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected PaystackService $paystack;

    public function __construct()
    {
        // Waste-collection invoices charge into the CUSTOMER's Paystack account
        $this->paystack = PaystackService::forCustomer();
    }

    /**
     * Initiate a payment for an invoice
     */
    public function initiate(Request $request, Invoice $invoice)
    {
        if ($invoice->status == 'Paid') {
            return redirect()->back()->with('error', 'Invoice is already paid.');
        }

        $email = 'customer@elitwaste.com'; // Default email if customer has none, Paystack requires email.
        // In future add email to Customer model if needed. 
        
        $callbackUrl = route('payments.callback');
        $amount = $invoice->balance_due; 
        
        $metadata = [
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'custom_fields' => [
                [
                    'display_name' => "Invoice Number",
                    'variable_name' => "invoice_number",
                    'value' => $invoice->invoice_number
                ]
            ]
        ];

        $response = $this->paystack->initializeTransaction($email, $amount, $callbackUrl, $metadata);

        if ($response['status'] && isset($response['data']['authorization_url'])) {
            return redirect($response['data']['authorization_url']);
        }

        return redirect()->back()->with('error', 'Unable to initialize payment: ' . ($response['message'] ?? 'Unknown error'));
    }

    /**
     * Handle Paystack Callback
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        
        if (!$reference) {
            return redirect()->route('dashboard')->with('error', 'No payment reference provided.');
        }

        $response = $this->paystack->verifyTransaction($reference);

        if ($response['status'] && $response['data']['status'] == 'success') {
            $data = $response['data'];
            $metadata = $data['metadata'];
            $invoiceId = $metadata['invoice_id'] ?? null;
            // Settle at the pre-fee base; the gross charge includes the Paystack
            // processing fee the payer absorbed, which must not credit the invoice.
            $amountPaid = isset($metadata['base_amount']) ? (float) $metadata['base_amount'] : $data['amount'] / 100;

            // Check if payment already recorded
            if (Payment::where('reference', $reference)->exists()) {
                 return redirect()->route('invoices.show', $invoiceId)->with('success', 'Payment already verified.');
            }

            // Record Payment
            $payment = Payment::create([
                'customer_id' => $metadata['customer_id'] ?? null,
                'invoice_id' => $invoiceId,
                'reference' => $reference,
                'amount' => $amountPaid,
                'status' => 'Success',
                'channel' => 'Paystack',
                'paid_at' => now(),
                'recorded_by' => null, // Online payment
                'metadata' => json_encode($data),
            ]);

            // Update Invoice
            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    // Update Balance
                    $invoice->balance_due -= $amountPaid;

                    if ($invoice->balance_due <= 0) {
                        $invoice->status = 'Paid';
                        $invoice->balance_due = 0; // Ensure no negative
                    } else {
                        $invoice->status = 'Partial';
                    }
                    $invoice->save();

                    // Send SMS
                    if ($invoice->customer && $invoice->customer->phone) {
                        $amount = number_format($amountPaid, 2);
                        $msg = "Payment Received: GHS {$amount} for Invoice #{$invoice->invoice_number}. Thanks! - Elite Waste";
                        \App\Jobs\SendSmsJob::dispatch($invoice->customer->phone, $msg);
                    }
                    
                    return redirect()->route('invoices.show', $invoice)->with('success', 'Payment successful! Invoice updated.');
                }
            }
            
            return redirect()->route('dashboard')->with('success', 'Payment successful!');
        }

        return redirect()->route('dashboard')->with('error', 'Payment verification failed.');
    }

    /**
     * Record Cash Payment (Admin)
     */
    public function recordCash(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
        ]);

        $amount = $request->amount;
        
        $payment = Payment::create([
            'customer_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
            'reference' => 'CASH-' . time() . '-' . $invoice->id,
            'amount' => $amount,
            'status' => 'Success',
            'channel' => 'Cash',
            'paid_at' => now(),
            'recorded_by' => Auth::id(),
        ]);
        
        \App\Services\AuditService::log('Recorded Cash Payment', "Invoice: {$invoice->invoice_number}, Amount: {$amount}");

        // Update Invoice Balance
        $invoice->balance_due -= $payment->amount;
        
        if ($invoice->balance_due <= 0) {
            $invoice->status = 'Paid';
            $invoice->balance_due = 0;
        } else {
            $invoice->status = 'Partial';
        }
        $invoice->save();

        // Send SMS
        if ($invoice->customer && $invoice->customer->phone) {
            $amount = number_format($payment->amount, 2);
            $msg = "Cash Payment Received: GHS {$amount} for Invoice #{$invoice->invoice_number}. recorded by " . auth()->user()->name . ". Thanks! - Elite Waste";
            \App\Jobs\SendSmsJob::dispatch($invoice->customer->phone, $msg);
        }

        return redirect()->back()->with('success', "Cash payment of GHS {$request->amount} recorded.");
    }

    public function print(Payment $payment)
    {
        $payment->load(['customer', 'invoice']);
        return view('payments.print', compact('payment'));
    }
}
