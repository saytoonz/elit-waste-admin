<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\PaystackService;
use Illuminate\Http\Request;

class PublicPaymentController extends Controller
{
    protected PaystackService $paystack;

    public function __construct()
    {
        // Public pay links also charge into the CUSTOMER's Paystack account
        $this->paystack = PaystackService::forCustomer();
    }

    public function show(Invoice $invoice)
    {
        if ($invoice->status == 'Paid') {
            return view('public.payment-status', ['message' => 'This invoice has already been paid. Thank you!', 'type' => 'success']);
        }

        return view('public.pay', compact('invoice'));
    }

    public function process(Request $request, Invoice $invoice)
    {
        if ($invoice->status == 'Paid') {
             return redirect()->route('public.pay.show', $invoice);
        }

        $email = 'customer@elitwaste.com'; // Default or grab from customer if available
        if ($invoice->customer && $invoice->customer->email) {
            $email = $invoice->customer->email;
        }

        // Use a generic callback that can handle public implementation
        // For simplicity reusing the main app callback but we might need a specific public success page
        // Let's stick to the main one for verification logic, BUT we need to handle the redirection after verify.
        // The existing PaymentController@callback redirects to 'dashboard' or 'invoices.show', which requires Auth. 
        // We will need a public callback or Modify the existing one to handle Guests.
        
        $callbackUrl = route('public.pay.callback');
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

        return back()->with('error', 'Unable to initialize payment.');
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        if (!$reference) {
            return view('public.payment-status', ['message' => 'No reference provided.', 'type' => 'error']);
        }

        $response = $this->paystack->verifyTransaction($reference);

        if ($response['status'] && $response['data']['status'] == 'success') {
            $data = $response['data'];
            $metadata = $data['metadata'];
            $invoiceId = $metadata['invoice_id'] ?? null;

            // Record Logic (Duplicated from PaymentController - should ideally be in a Service)
            // For speed, let's defer to the PaymentController logic if we can, or just duplicate relevant parts.
            // Let's duplicate essential recording logic here to avoid Auth issues.
            
            $payment = \App\Models\Payment::where('reference', $reference)->first();
            if ($payment) {
                 return view('public.payment-status', ['message' => 'Payment already verified.', 'type' => 'success']);
            }

            // Record
             \App\Models\Payment::create([
                'customer_id' => $metadata['customer_id'] ?? null,
                'invoice_id' => $invoiceId,
                'reference' => $reference,
                'amount' => $data['amount'] / 100,
                'status' => 'Success',
                'channel' => 'Paystack',
                'paid_at' => now(),
                'recorded_by' => null,
                'metadata' => json_encode($data),
            ]);

            if ($invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    $invoice->balance_due -= ($data['amount'] / 100);
                    if ($invoice->balance_due <= 0) {
                        $invoice->status = 'Paid';
                        $invoice->balance_due = 0;
                    } else {
                        $invoice->status = 'Partial';
                    }
                    $invoice->save();
                    
                    \App\Services\AuditService::log('Online Payment', "Public Link for #{$invoice->invoice_number}");
                }
            }

            return view('public.payment-status', ['message' => 'Payment Successful! Thank you.', 'type' => 'success']);
        }

        return view('public.payment-status', ['message' => 'Payment verification failed.', 'type' => 'error']);
    }
}
