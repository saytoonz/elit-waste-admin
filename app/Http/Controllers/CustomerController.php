<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Zone;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query()->with('zone');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }
        
        if ($request->filled('status')) {
             $query->where('is_active', $request->status == 'active');
        }

        $customers = $query->latest()->paginate(10)->withQueryString();
        $zones = Zone::where('is_active', true)->get();

        return view('customers.index', compact('customers', 'zones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $zones = Zone::where('is_active', true)->get();
        $servicePlans = \App\Models\ServicePlan::where('is_active', true)->get();
        return view('customers.create', compact('zones', 'servicePlans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers',
            'secondary_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'landmark' => 'nullable|string|max:255',
            'zone_id' => 'nullable|exists:zones,id',
            'gps_coordinates' => 'nullable|string',
            'type' => 'required|in:Residential,Commercial',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $customer = \App\Models\Customer::create($validated);
        
        \App\Services\AuditService::log('Created Customer', "Name: {$validated['name']}");

        // Auto-create subscription if plan selected
        if ($request->filled('service_plan_id')) {
            $plan = \App\Models\ServicePlan::find($request->service_plan_id);
            if ($plan) {
                $customer->subscription()->create([
                    'service_plan_id' => $plan->id,
                    'billing_cycle' => $plan->billing_cycle,
                    'amount' => $plan->amount,
                    'status' => 'Active',
                    'start_date' => now(),
                    'next_billing_date' => now()->addMonth(), // Default to 1 month, adjust logic later based on cycle
                ]);
                
                // Adjust next billing date
                $sub = $customer->subscription;
                if ($plan->billing_cycle == 'Weekly') $sub->next_billing_date = now()->addWeek();
                if ($plan->billing_cycle == 'Quarterly') $sub->next_billing_date = now()->addMonths(3);
                if ($plan->billing_cycle == 'Yearly') $sub->next_billing_date = now()->addYear();
                $sub->save();
            }
        }

        // Send Welcome SMS
        if ($validated['phone']) {
            $template = \App\Models\Setting::where('key', 'sms_welcome_template')->value('value');
            
            if (!$template) {
                // Fallback if not set
                $template = "Welcome to Elite Waste, {firstname}! Your service for {service_type} has been registered.";
            }

            // Extract first name for variable
            $parts = explode(' ', trim($validated['name']));
            $firstName = $parts[0] ?? 'Valued Customer';
            $lastName = $parts[1] ?? '';

            // Replace variables
            $msg = str_replace(
                ['{firstname}', '{lastname}', '{service_type}'],
                [$firstName, $lastName, $validated['type']],
                $template
            );

            \App\Jobs\SendSmsJob::dispatch($validated['phone'], $msg);
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $zones = Zone::where('is_active', true)->get();
        $servicePlans = \App\Models\ServicePlan::where('is_active', true)->get();
        return view('customers.edit', compact('customer', 'zones', 'servicePlans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'secondary_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'landmark' => 'nullable|string|max:255',
            'zone_id' => 'nullable|exists:zones,id',
            'gps_coordinates' => 'nullable|string',
            'type' => 'required|in:Residential,Commercial',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $validated['is_active'] = $request->has('is_active');

        $customer->update($validated);
        \App\Services\AuditService::log('Updated Customer', "ID: {$customer->id} Name: {$customer->name}");

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        \App\Services\AuditService::log('Deleted Customer', "ID: {$customer->id} Name: {$customer->name}");

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
