<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::query()->with('customer');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        return view('invoices.create', compact('customers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['invoice_number'] = 'INV-' . strtoupper(Str::random(8));
        $validated['balance_due'] = $validated['amount'];
        $validated['status'] = 'Pending';

        $invoice = Invoice::create($validated);
        \App\Services\AuditService::log('Created Invoice', "Number: {$invoice->invoice_number}");

        // Send SMS
        if ($invoice->customer && $invoice->customer->phone) {
             $amount = number_format($invoice->amount, 2);
             $link = route('public.pay.show', $invoice); // Generate public link
             $msg = "New Invoice #{$invoice->invoice_number} Generated. Amount: GHS {$amount}. Pay here: {$link}";
             \App\Jobs\SendSmsJob::dispatch($invoice->customer->phone, $msg);
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        return view('invoices.edit', compact('invoice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'required|in:Pending,Partial,Paid,Overdue,Cancelled',
            'notes' => 'nullable|string',
        ]);

        // If status is Paid, balance should be 0? Or user handles it.
        // For simple MVP, user manually updates status. 
        // We will improve this with Payments module later (auto update balance).

        if ($validated['status'] == 'Paid') {
            $validated['balance_due'] = 0;
        } elseif ($invoice->status == 'Paid' && $validated['status'] != 'Paid') {
             // Reverting from Paid...? Ideally don't allow easily without audit.
             // Let's assume user knows what they are doing for admin dashboard.
             $validated['balance_due'] = $validated['amount']; 
        }

        $invoice->update($validated);
        \App\Services\AuditService::log('Updated Invoice', "Number: {$invoice->invoice_number}");

        return redirect()->route('invoices.index')->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        \App\Services\AuditService::log('Deleted Invoice', "Number: {$invoice->invoice_number}");
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted.');
    }
}
