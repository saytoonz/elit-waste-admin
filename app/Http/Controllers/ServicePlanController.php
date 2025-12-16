<?php

namespace App\Http\Controllers;

use App\Models\ServicePlan;
use Illuminate\Http\Request;

class ServicePlanController extends Controller
{
    public function index()
    {
        $plans = ServicePlan::all();
        return view('service_plans.index', compact('plans'));
    }

    public function create()
    {
        return view('service_plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:Weekly,Monthly,Quarterly,Yearly',
            'description' => 'nullable|string',
        ]);

        $plan = ServicePlan::create($request->all());

        \App\Services\AuditService::log('Created Service Plan', "Name: {$plan->name}");

        return redirect()->route('service_plans.index')->with('success', 'Service Plan created successfully.');
    }

    public function edit(ServicePlan $servicePlan)
    {
        return view('service_plans.edit', ['plan' => $servicePlan]);
    }

    public function update(Request $request, ServicePlan $servicePlan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:Weekly,Monthly,Quarterly,Yearly',
            'description' => 'nullable|string',
        ]);

        $servicePlan->update($request->all());

        \App\Services\AuditService::log('Updated Service Plan', "Name: {$servicePlan->name}");

        return redirect()->route('service_plans.index')->with('success', 'Service Plan updated successfully.');
    }

    public function destroy(ServicePlan $servicePlan)
    {
        $servicePlan->delete();
        \App\Services\AuditService::log('Deleted Service Plan', "Name: {$servicePlan->name}");
        return redirect()->route('service_plans.index')->with('success', 'Service Plan deleted successfully.');
    }
}
