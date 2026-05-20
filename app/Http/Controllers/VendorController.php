<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Services\AuditService;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query()->withCount('expenses')->withSum('expenses as total_spent', 'total_amount');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('tax_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $vendors = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $vendor = Vendor::create($validated);
        AuditService::log('Created Vendor', $vendor->name);
        return redirect()->route('vendors.index')->with('success', 'Vendor created.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['expenses' => fn($q) => $q->latest('expense_date')->limit(50), 'expenses.category']);
        $totalSpent = $vendor->expenses()->approvedOrPaid()->sum('total_amount');
        $pendingAmount = $vendor->expenses()->where('status', 'Pending')->sum('total_amount');
        $byCategory = $vendor->expenses()
            ->selectRaw('expense_category_id, sum(total_amount) as total')
            ->with('category')
            ->groupBy('expense_category_id')
            ->get();
        return view('vendors.show', compact('vendor', 'totalSpent', 'pendingAmount', 'byCategory'));
    }

    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $this->validatePayload($request);
        $validated['is_active'] = $request->has('is_active');
        $vendor->update($validated);
        AuditService::log('Updated Vendor', $vendor->name);
        return redirect()->route('vendors.index')->with('success', 'Vendor updated.');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->expenses()->exists()) {
            return back()->with('error', 'Cannot delete: vendor has expense history. Deactivate instead.');
        }
        $name = $vendor->name;
        $vendor->delete();
        AuditService::log('Deleted Vendor', $name);
        return back()->with('success', 'Vendor deleted.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:255',
            'tax_id'         => 'nullable|string|max:60',
            'address'        => 'nullable|string|max:500',
            'notes'          => 'nullable|string|max:1000',
            'is_active'      => 'sometimes|boolean',
        ]);
    }
}
