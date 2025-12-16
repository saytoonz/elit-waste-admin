<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:Weekly,Monthly,Quarterly',
            'start_date' => 'required|date',
            'due_date_offset_days' => 'required|integer|min:0',
        ]);

        // Determine next billing date (initially start date or next cycle?)
        // Usually start date is the first billing date.
        $validated['next_billing_date'] = $validated['start_date'];
        $validated['status'] = 'Active';

        Subscription::create($validated);

        return redirect()->back()->with('success', 'Subscription plan created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:Weekly,Monthly,Quarterly',
            'due_date_offset_days' => 'required|integer|min:0',
            'status' => 'required|in:Active,Inactive',
        ]);

        // If cycle changes, we might want to recalculate next billing date? 
        // For simplicity, let's keep next date as is unless manually changed, or just update core fields.
        
        $subscription->update($validated);

        return redirect()->back()->with('success', 'Subscription updated successfully.');
    }

    /**
     * Remove the specified resource from storage (Cancel).
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return redirect()->back()->with('success', 'Subscription deleted.');
    }
}
