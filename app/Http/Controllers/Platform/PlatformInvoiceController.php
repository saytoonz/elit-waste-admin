<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\PlatformInvoice;
use App\Services\AuditService;
use Illuminate\Http\Request;

class PlatformInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = PlatformInvoice::with('items.service');

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('currency')) $query->where('currency', $request->currency);
        if ($request->filled('search'))   $query->where('invoice_number', 'like', '%' . $request->search . '%');

        $invoices = $query->latest('issued_at')->paginate(30)->withQueryString();

        $totals = [
            'unpaid_count' => PlatformInvoice::unpaid()->count(),
            'overdue_count' => PlatformInvoice::where('status', 'Overdue')->count(),
        ];

        return view('platform.invoices.index', compact('invoices', 'totals'));
    }

    public function show(PlatformInvoice $invoice)
    {
        $invoice->load(['items.service', 'items.subscription', 'payments.recordedBy']);
        return view('platform.invoices.show', compact('invoice'));
    }

    public function markPaid(Request $request, PlatformInvoice $invoice, \App\Services\PlatformBillingService $billing)
    {
        $data = $request->validate([
            'amount'    => 'required|numeric|min:0.01',
            'channel'   => 'required|string|max:60',
            'reference' => 'nullable|string|max:120',
        ]);

        $ref = $data['reference'] ?: 'MANUAL-' . time();
        $billing->applyPayment($invoice, (float) $data['amount'], $ref, $data['channel'], ['source' => 'manual']);
        AuditService::log('Manually Marked Platform Invoice Paid', "#{$invoice->invoice_number} {$invoice->currency} {$data['amount']}");
        return back()->with('success', 'Payment recorded.');
    }

    public function cancel(PlatformInvoice $invoice)
    {
        if ($invoice->status === 'Paid') return back()->with('error', 'Cannot cancel a paid invoice.');
        $invoice->update(['status' => 'Cancelled']);
        AuditService::log('Cancelled Platform Invoice', "#{$invoice->invoice_number}");
        return back()->with('success', 'Invoice cancelled.');
    }
}
