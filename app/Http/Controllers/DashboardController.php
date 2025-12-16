<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {

        // KPI Cards
        $totalCustomers = Customer::count();
        $totalRevenue = \App\Models\Payment::where('status', 'Success')->sum('amount');
        $activeInvoices = Invoice::where('status', '!=', 'Paid')->count();
        $pendingCash = \App\Models\Payment::where('channel', 'Cash')
                                          ->whereNull('metadata->approved_at') // Use metadata or new column later
                                          ->sum('amount'); // Placeholder for now

        // Chart Data: Revenue Last 6 Months
        $revenueData = \App\Models\Payment::selectRaw('DATE_FORMAT(paid_at, "%Y-%m") as month, sum(amount) as total')
            ->where('status', 'Success')
            ->where('paid_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $chartLabels = $revenueData->keys()->toJson();
        $chartValues = $revenueData->values()->toJson();

        // Chart Data: Customers by Zone
        $zoneData = Customer::selectRaw('zone_id, count(*) as total')
            ->with('zone')
            ->groupBy('zone_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->zone->name ?? 'Unknown',
                    'count' => $item->total
                ];
            });
            
        $zoneLabels = $zoneData->pluck('name')->toJson();
        $zoneValues = $zoneData->pluck('count')->toJson();

        // Recent Activity
        $recentPayments = \App\Models\Payment::with(['customer', 'invoice'])->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalCustomers', 
            'totalRevenue', 
            'activeInvoices',
            'chartLabels',
            'chartValues',
            'zoneLabels',
            'zoneValues',
            'recentPayments'
        ));
    }
}
