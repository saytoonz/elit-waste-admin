<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\RecurringExpense;
use App\Models\Vendor;
use App\Models\Zone;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecurringExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = RecurringExpense::with(['category', 'vendor']);

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('frequency')) {
            $query->where('frequency', $request->frequency);
        }

        $recurring = $query->orderBy('next_run_date')->paginate(20)->withQueryString();
        return view('recurring_expenses.index', compact('recurring'));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $vendors    = Vendor::where('is_active', true)->orderBy('name')->get();
        $zones      = Zone::where('is_active', true)->orderBy('name')->get();
        return view('recurring_expenses.create', compact('categories', 'vendors', 'zones'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $validated['next_run_date'] = $validated['next_run_date'] ?? $validated['start_date'];
        $validated['created_by'] = auth()->id();
        $recurring = RecurringExpense::create($validated);
        AuditService::log('Created Recurring Expense', "{$recurring->name} ({$recurring->frequency})");
        return redirect()->route('recurring_expenses.index')->with('success', 'Recurring expense created.');
    }

    public function edit(RecurringExpense $recurringExpense)
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $vendors    = Vendor::where('is_active', true)->orderBy('name')->get();
        $zones      = Zone::where('is_active', true)->orderBy('name')->get();
        return view('recurring_expenses.edit', [
            'recurring' => $recurringExpense,
            'categories' => $categories,
            'vendors' => $vendors,
            'zones' => $zones,
        ]);
    }

    public function update(Request $request, RecurringExpense $recurringExpense)
    {
        $validated = $this->validatePayload($request);
        $validated['is_active'] = $request->has('is_active');
        $validated['auto_approve'] = $request->has('auto_approve');
        $recurringExpense->update($validated);
        AuditService::log('Updated Recurring Expense', $recurringExpense->name);
        return redirect()->route('recurring_expenses.index')->with('success', 'Recurring expense updated.');
    }

    public function destroy(RecurringExpense $recurringExpense)
    {
        $name = $recurringExpense->name;
        $recurringExpense->delete();
        AuditService::log('Deleted Recurring Expense', $name);
        return back()->with('success', 'Recurring expense deleted.');
    }

    public function runNow(RecurringExpense $recurringExpense)
    {
        $expense = $this->generateExpenseFrom($recurringExpense);
        $recurringExpense->last_run_date = now()->toDateString();
        $recurringExpense->advanceNextRunDate();
        $recurringExpense->save();
        AuditService::log('Ran Recurring Expense', "{$recurringExpense->name} → #{$expense->expense_number}");
        return back()->with('success', "Recurring expense executed. Created #{$expense->expense_number}");
    }

    public static function generateExpenseFrom(RecurringExpense $recurring): Expense
    {
        $autoApprove = $recurring->auto_approve;
        $now = now();
        return Expense::create([
            'expense_number'       => 'EXP-' . date('Ym') . '-' . strtoupper(Str::random(5)),
            'expense_category_id'  => $recurring->expense_category_id,
            'vendor_id'            => $recurring->vendor_id,
            'zone_id'              => $recurring->zone_id,
            'recurring_expense_id' => $recurring->id,
            'expense_date'         => $recurring->next_run_date,
            'amount'               => $recurring->amount,
            'tax_amount'           => $recurring->tax_amount,
            'total_amount'         => $recurring->amount + $recurring->tax_amount,
            'payment_method'       => $recurring->payment_method,
            'status'               => $autoApprove ? 'Approved' : 'Pending',
            'description'          => $recurring->description,
            'recorded_by'          => $recurring->created_by,
            'approved_at'          => $autoApprove ? $now : null,
            'approved_by'          => $autoApprove ? $recurring->created_by : null,
            'notes'                => "Auto-generated from recurring expense: {$recurring->name}",
        ]);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name'                => 'required|string|max:255',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'vendor_id'           => 'nullable|exists:vendors,id',
            'zone_id'             => 'nullable|exists:zones,id',
            'amount'              => 'required|numeric|min:0',
            'tax_amount'          => 'nullable|numeric|min:0',
            'frequency'           => 'required|in:Daily,Weekly,Monthly,Quarterly,Yearly',
            'start_date'          => 'required|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'next_run_date'       => 'nullable|date',
            'payment_method'      => 'required|in:Cash,Bank Transfer,Mobile Money,Card,Cheque,Other',
            'auto_approve'        => 'sometimes|boolean',
            'is_active'           => 'sometimes|boolean',
            'description'         => 'required|string|max:500',
            'notes'               => 'nullable|string|max:1000',
        ]);
    }
}
