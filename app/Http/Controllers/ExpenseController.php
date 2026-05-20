<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Vendor;
use App\Models\Zone;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::query()->with(['category', 'vendor', 'zone', 'recordedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($v) => $v->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        $expenses = $query->latest('expense_date')->paginate(20)->withQueryString();

        // Stat strip (respects current filters' totals)
        $statQuery = clone $query;
        $totals = [
            'count'    => (clone $query)->count(),
            'total'    => (clone $query)->sum('total_amount'),
            'pending'  => (clone $query)->where('status', 'Pending')->sum('total_amount'),
            'approved' => (clone $query)->whereIn('status', ['Approved', 'Paid'])->sum('total_amount'),
        ];

        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $vendors    = Vendor::where('is_active', true)->orderBy('name')->get();
        $zones      = Zone::where('is_active', true)->orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'totals', 'categories', 'vendors', 'zones'));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $vendors    = Vendor::where('is_active', true)->orderBy('name')->get();
        $zones      = Zone::where('is_active', true)->orderBy('name')->get();
        return view('expenses.create', compact('categories', 'vendors', 'zones'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateExpense($request);
        $validated['expense_number'] = $this->generateExpenseNumber();
        $validated['total_amount']   = ($validated['amount'] ?? 0) + ($validated['tax_amount'] ?? 0);
        $validated['recorded_by']    = auth()->id();
        $validated['status']         = $request->input('status', 'Pending');

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('expenses/' . date('Y/m'), 'public');
            $validated['attachment_path'] = $path;
            $validated['attachment_name'] = $file->getClientOriginalName();
        }

        $expense = Expense::create($validated);
        AuditService::log('Created Expense', "#{$expense->expense_number} GHS {$expense->total_amount}");

        return redirect()->route('expenses.show', $expense)->with('success', 'Expense recorded successfully.');
    }

    public function show(Expense $expense)
    {
        $expense->load(['category.parent', 'vendor', 'zone', 'recordedBy', 'approvedBy', 'recurringExpense']);
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        if (in_array($expense->status, ['Paid', 'Cancelled'])) {
            return redirect()->route('expenses.show', $expense)->with('error', 'Paid or cancelled expenses cannot be edited.');
        }
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $vendors    = Vendor::where('is_active', true)->orderBy('name')->get();
        $zones      = Zone::where('is_active', true)->orderBy('name')->get();
        return view('expenses.edit', compact('expense', 'categories', 'vendors', 'zones'));
    }

    public function update(Request $request, Expense $expense)
    {
        if (in_array($expense->status, ['Paid', 'Cancelled'])) {
            return redirect()->route('expenses.show', $expense)->with('error', 'Paid or cancelled expenses cannot be edited.');
        }

        $validated = $this->validateExpense($request);
        $validated['total_amount'] = ($validated['amount'] ?? 0) + ($validated['tax_amount'] ?? 0);

        if ($request->hasFile('attachment')) {
            if ($expense->attachment_path) {
                Storage::disk('public')->delete($expense->attachment_path);
            }
            $file = $request->file('attachment');
            $path = $file->store('expenses/' . date('Y/m'), 'public');
            $validated['attachment_path'] = $path;
            $validated['attachment_name'] = $file->getClientOriginalName();
        }

        // Resetting an edited approved expense back to Pending forces re-approval
        if ($expense->status === 'Approved') {
            $validated['status']      = 'Pending';
            $validated['approved_at'] = null;
            $validated['approved_by'] = null;
        }

        $expense->update($validated);
        AuditService::log('Updated Expense', "#{$expense->expense_number}");

        return redirect()->route('expenses.show', $expense)->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->status === 'Paid') {
            return back()->with('error', 'Paid expenses cannot be deleted. Cancel instead.');
        }
        $number = $expense->expense_number;
        $expense->delete();
        AuditService::log('Deleted Expense', "#{$number}");
        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    public function approve(Expense $expense)
    {
        if ($expense->status !== 'Pending') {
            return back()->with('error', 'Only pending expenses can be approved.');
        }
        $expense->update([
            'status'      => 'Approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejection_reason' => null,
        ]);
        AuditService::log('Approved Expense', "#{$expense->expense_number} GHS {$expense->total_amount}");
        return back()->with('success', 'Expense approved.');
    }

    public function reject(Request $request, Expense $expense)
    {
        $data = $request->validate(['rejection_reason' => 'required|string|max:500']);
        if ($expense->status !== 'Pending') {
            return back()->with('error', 'Only pending expenses can be rejected.');
        }
        $expense->update([
            'status'           => 'Rejected',
            'rejection_reason' => $data['rejection_reason'],
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
        ]);
        AuditService::log('Rejected Expense', "#{$expense->expense_number}: {$data['rejection_reason']}");
        return back()->with('success', 'Expense rejected.');
    }

    public function markPaid(Expense $expense)
    {
        if (!in_array($expense->status, ['Approved', 'Pending'])) {
            return back()->with('error', 'Only approved or pending expenses can be marked paid.');
        }
        $expense->update([
            'status'  => 'Paid',
            'paid_at' => now(),
            'approved_at' => $expense->approved_at ?? now(),
            'approved_by' => $expense->approved_by ?? auth()->id(),
        ]);
        AuditService::log('Marked Expense Paid', "#{$expense->expense_number} GHS {$expense->total_amount}");
        return back()->with('success', 'Expense marked as paid.');
    }

    public function cancel(Expense $expense)
    {
        if ($expense->status === 'Paid') {
            return back()->with('error', 'Paid expenses cannot be cancelled.');
        }
        $expense->update(['status' => 'Cancelled']);
        AuditService::log('Cancelled Expense', "#{$expense->expense_number}");
        return back()->with('success', 'Expense cancelled.');
    }

    public function downloadAttachment(Expense $expense)
    {
        if (!$expense->attachment_path || !Storage::disk('public')->exists($expense->attachment_path)) {
            abort(404);
        }
        return Storage::disk('public')->download($expense->attachment_path, $expense->attachment_name);
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'vendor_id'           => 'nullable|exists:vendors,id',
            'zone_id'             => 'nullable|exists:zones,id',
            'expense_date'        => 'required|date',
            'amount'              => 'required|numeric|min:0',
            'tax_amount'          => 'nullable|numeric|min:0',
            'payment_method'      => 'required|in:Cash,Bank Transfer,Mobile Money,Card,Cheque,Other',
            'reference'           => 'nullable|string|max:255',
            'description'         => 'required|string|max:1000',
            'notes'               => 'nullable|string|max:2000',
            'attachment'          => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'status'              => 'nullable|in:Draft,Pending',
        ]);
    }

    private function generateExpenseNumber(): string
    {
        do {
            $number = 'EXP-' . date('Ym') . '-' . strtoupper(Str::random(5));
        } while (Expense::where('expense_number', $number)->exists());
        return $number;
    }
}
