<?php

namespace App\Http\Controllers;

use App\Models\ExpenseBudget;
use App\Models\ExpenseCategory;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ExpenseBudgetController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));
        $month = $request->filled('month') ? (int) $request->month : null;

        $query = ExpenseBudget::with('category')->where('year', $year);
        if ($month) {
            $query->where('month', $month);
        }

        $budgets = $query->orderBy('expense_category_id')->paginate(30)->withQueryString();

        $availableYears = range(date('Y') - 2, date('Y') + 1);

        return view('expense_budgets.index', compact('budgets', 'year', 'month', 'availableYears'));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        return view('expense_budgets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $validated['created_by'] = auth()->id();

        // Auto-derive quarter if Quarterly
        if ($validated['period'] === 'Quarterly' && !empty($validated['month'])) {
            $validated['quarter'] = (int) ceil($validated['month'] / 3);
            $validated['month'] = null;
        }
        if ($validated['period'] === 'Yearly') {
            $validated['month'] = null;
            $validated['quarter'] = null;
        }

        $budget = ExpenseBudget::create($validated);
        AuditService::log('Created Budget', "Category: {$budget->category->name} GHS {$budget->amount}");
        return redirect()->route('expense_budgets.index')->with('success', 'Budget set.');
    }

    public function edit(ExpenseBudget $expenseBudget)
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        return view('expense_budgets.edit', ['budget' => $expenseBudget, 'categories' => $categories]);
    }

    public function update(Request $request, ExpenseBudget $expenseBudget)
    {
        $validated = $this->validatePayload($request);
        if ($validated['period'] === 'Quarterly' && !empty($validated['month'])) {
            $validated['quarter'] = (int) ceil($validated['month'] / 3);
            $validated['month'] = null;
        }
        if ($validated['period'] === 'Yearly') {
            $validated['month'] = null;
            $validated['quarter'] = null;
        }
        $expenseBudget->update($validated);
        AuditService::log('Updated Budget', "Category: {$expenseBudget->category->name}");
        return redirect()->route('expense_budgets.index')->with('success', 'Budget updated.');
    }

    public function destroy(ExpenseBudget $expenseBudget)
    {
        $expenseBudget->delete();
        AuditService::log('Deleted Budget', "Category: {$expenseBudget->category->name}");
        return back()->with('success', 'Budget deleted.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'expense_category_id'      => 'required|exists:expense_categories,id',
            'period'                   => 'required|in:Monthly,Quarterly,Yearly',
            'year'                     => 'required|integer|min:2020|max:2100',
            'month'                    => 'nullable|integer|min:1|max:12',
            'amount'                   => 'required|numeric|min:0',
            'alert_enabled'            => 'sometimes|boolean',
            'alert_threshold_percent'  => 'nullable|integer|min:1|max:100',
            'notes'                    => 'nullable|string|max:500',
        ]);
    }
}
