<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
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
        $totalRevenue   = \App\Models\Payment::where('status', 'Success')->sum('amount');
        $activeInvoices = Invoice::where('status', '!=', 'Paid')->count();

        // Expense KPIs
        $monthStart = now()->startOfMonth();
        $monthEnd   = now()->endOfMonth();
        $monthExpenses = Expense::approvedOrPaid()->between($monthStart, $monthEnd)->sum('total_amount');
        $monthRevenue  = \App\Models\Payment::where('status', 'Success')
            ->whereBetween('paid_at', [$monthStart, $monthEnd])->sum('amount');
        $monthNet = $monthRevenue - $monthExpenses;
        $pendingExpenses = Expense::where('status', 'Pending')->sum('total_amount');

        // Revenue trend (last 6 months)
        $revenueData = \App\Models\Payment::selectRaw('DATE_FORMAT(paid_at, "%Y-%m") as month, sum(amount) as total')
            ->where('status', 'Success')
            ->where('paid_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Expense trend (last 6 months) — align labels with revenue
        $expenseData = Expense::selectRaw('DATE_FORMAT(expense_date, "%Y-%m") as month, sum(total_amount) as total')
            ->approvedOrPaid()
            ->where('expense_date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $monthsRange = collect();
        for ($i = 5; $i >= 0; $i--) {
            $monthsRange->push(now()->subMonths($i)->format('Y-m'));
        }

        $chartLabels = $monthsRange->map(fn($m) => \Carbon\Carbon::parse($m . '-01')->format('M Y'))->toJson();
        $chartValues = $monthsRange->map(fn($m) => (float) ($revenueData[$m] ?? 0))->toJson();
        $expenseValues = $monthsRange->map(fn($m) => (float) ($expenseData[$m] ?? 0))->toJson();

        // Customers by Zone
        $zoneData = Customer::selectRaw('zone_id, count(*) as total')
            ->with('zone')
            ->groupBy('zone_id')
            ->get()
            ->map(fn($i) => ['name' => $i->zone->name ?? 'Unknown', 'count' => $i->total]);
        $zoneLabels = $zoneData->pluck('name')->toJson();
        $zoneValues = $zoneData->pluck('count')->toJson();

        // Expense by category (this month)
        $categoryBreakdown = Expense::selectRaw('expense_category_id, sum(total_amount) as total')
            ->approvedOrPaid()
            ->between($monthStart, $monthEnd)
            ->with('category')
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();
        $categoryLabels = $categoryBreakdown->pluck('category.name')->toJson();
        $categoryValues = $categoryBreakdown->pluck('total')->toJson();
        $categoryColors = $categoryBreakdown->pluck('category.color')->map(fn($c) => $c ?: '#6B7280')->toJson();

        // Recent activity
        $recentPayments = \App\Models\Payment::with(['customer', 'invoice'])->latest()->take(5)->get();
        $recentExpenses = Expense::with(['category', 'vendor'])->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalCustomers',
            'totalRevenue',
            'activeInvoices',
            'monthExpenses',
            'monthRevenue',
            'monthNet',
            'pendingExpenses',
            'chartLabels',
            'chartValues',
            'expenseValues',
            'zoneLabels',
            'zoneValues',
            'categoryLabels',
            'categoryValues',
            'categoryColors',
            'recentPayments',
            'recentExpenses'
        ));
    }
}
