<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::with('parent', 'children')
            ->withCount('expenses')
            ->orderBy('parent_id')
            ->orderBy('name')
            ->paginate(30);
        return view('expense_categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = ExpenseCategory::roots()->active()->orderBy('name')->get();
        return view('expense_categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $category = ExpenseCategory::create($validated);
        AuditService::log('Created Expense Category', $category->name);
        return redirect()->route('expense_categories.index')->with('success', 'Category created.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        $parents = ExpenseCategory::roots()->where('id', '!=', $expenseCategory->id)->active()->orderBy('name')->get();
        return view('expense_categories.edit', ['category' => $expenseCategory, 'parents' => $parents]);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $this->validatePayload($request, $expenseCategory->id);
        if (($validated['parent_id'] ?? null) == $expenseCategory->id) {
            $validated['parent_id'] = null;
        }
        $expenseCategory->update($validated);
        AuditService::log('Updated Expense Category', $expenseCategory->name);
        return redirect()->route('expense_categories.index')->with('success', 'Category updated.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->exists()) {
            return back()->with('error', 'Cannot delete: category has linked expenses.');
        }
        $name = $expenseCategory->name;
        $expenseCategory->delete();
        AuditService::log('Deleted Expense Category', $name);
        return back()->with('success', 'Category deleted.');
    }

    private function validatePayload(Request $request, ?int $id = null): array
    {
        $unique = 'unique:expense_categories,code' . ($id ? ",{$id}" : '');
        return $request->validate([
            'name'        => 'required|string|max:120',
            'code'        => "nullable|string|max:30|{$unique}",
            'parent_id'   => 'nullable|exists:expense_categories,id',
            'color'       => 'nullable|string|max:20',
            'icon'        => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'sometimes|boolean',
        ]);
    }
}
