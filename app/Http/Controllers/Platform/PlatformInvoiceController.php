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
        $view = $request->input('view', 'active'); // active | trashed | all
        $query = PlatformInvoice::with('items.service');

        if ($view === 'trashed') {
            $query->onlyTrashed();
        } elseif ($view === 'all') {
            $query->withTrashed();
        }

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('currency')) $query->where('currency', $request->currency);
        if ($request->filled('search'))   $query->where('invoice_number', 'like', '%' . $request->search . '%');

        $invoices = $query->latest('issued_at')->paginate(30)->withQueryString();

        $totals = [
            'unpaid_count'  => PlatformInvoice::unpaid()->count(),
            'overdue_count' => PlatformInvoice::where('status', 'Overdue')->count(),
            'trashed_count' => PlatformInvoice::onlyTrashed()->count(),
        ];

        return view('platform.invoices.index', compact('invoices', 'totals', 'view'));
    }

    public function show(int $invoice)
    {
        $invoice = PlatformInvoice::withTrashed()
            ->with(['items.service', 'items.subscription', 'payments.recordedBy'])
            ->findOrFail($invoice);
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

    /**
     * Soft-delete an invoice. Hidden from customer + provider lists by default;
     * provider can view via ?view=trashed and restore or force-delete.
     */
    public function destroy(PlatformInvoice $invoice)
    {
        if ($invoice->status === 'Paid' && (float) $invoice->amount_paid > 0) {
            return back()->with('error', 'Cannot delete a paid invoice — it has payment records. Use Cancel instead.');
        }
        $number = $invoice->invoice_number;
        $invoice->delete();
        AuditService::log('Deleted Platform Invoice', "#{$number}");
        return redirect()->route('platform.invoices.index')->with('success', "Invoice #{$number} deleted. Visible under Trash.");
    }

    public function restore(int $invoice)
    {
        $invoice = PlatformInvoice::onlyTrashed()->findOrFail($invoice);
        $invoice->restore();
        AuditService::log('Restored Platform Invoice', "#{$invoice->invoice_number}");
        return back()->with('success', "Invoice #{$invoice->invoice_number} restored.");
    }

    /**
     * Permanent delete — only from the Trash view. Cascade-deletes items + payments via DB FKs.
     */
    public function forceDelete(int $invoice)
    {
        $invoice = PlatformInvoice::onlyTrashed()->findOrFail($invoice);
        $number = $invoice->invoice_number;
        $invoice->forceDelete();
        AuditService::log('Permanently Deleted Platform Invoice', "#{$number}");
        return redirect()->route('platform.invoices.index', ['view' => 'trashed'])
            ->with('success', "Invoice #{$number} permanently deleted.");
    }
}
