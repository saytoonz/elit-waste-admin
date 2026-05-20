<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function receivables(Request $request)
    {
        $query = Customer::query()->with('zone')->whereHas('invoices', function($q) {
            $q->where('balance_due', '>', 0);
        });

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }

        // Calculate total debt for each customer
        // We can do this efficiently with subquery or aggregation, 
        // but for now let's just use withSum for Simplicity
        $customers = $query->withSum(['invoices' => function($q) {
            $q->where('balance_due', '>', 0);
        }], 'balance_due')
        ->orderByDesc('invoices_sum_balance_due')
        ->paginate(20)
        ->withQueryString();

        $zones = Zone::where('is_active', true)->get();
        $totalReceivables = Invoice::sum('balance_due');

        return view('reports.receivables', compact('customers', 'zones', 'totalReceivables'));
    }

    public function revenue(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $payments = Payment::whereBetween('paid_at', [$startDate, $endDate])
            ->selectRaw('DATE(paid_at) as date, sum(amount) as total, channel')
            ->groupBy('date', 'channel')
            ->orderBy('date')
            ->get();

        $totalRevenue = Payment::whereBetween('paid_at', [$startDate, $endDate])->sum('amount');
        
        // Prepare Chart Data
        $chartData = Payment::whereBetween('paid_at', [$startDate, $endDate])
            ->selectRaw('DATE(paid_at) as date, sum(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartLabels = $chartData->pluck('date')->map(function($date){
            return \Carbon\Carbon::parse($date)->format('M d');
        })->toJson();
        $chartValues = $chartData->pluck('total')->toJson();

        return view('reports.revenue', compact('payments', 'totalRevenue', 'startDate', 'endDate', 'chartLabels', 'chartValues'));
    }

    public function payments(Request $request)
    {
        $query = Payment::query()->with(['customer', 'invoice']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('reference', 'like', "%{$search}%");
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        
         if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('paid_at', [$request->start_date, $request->end_date]);
        }

        $payments = $query->latest('paid_at')->paginate(20)->withQueryString();

        return view('reports.payments', compact('payments'));
    }
    
    public function pendingCash(Request $request)
    {
        $payments = \App\Models\Payment::where('channel', 'Cash')
            ->whereNull('approved_at')
            ->with(['invoice', 'customer'])
            ->latest()
            ->paginate(20);
            
        return view('reports.cash_approval', compact('payments'));
    }

    public function audit(Request $request)
    {
        $query = \App\Models\AuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();
        $users = \App\Models\User::orderBy('name')->get();

        return view('reports.audit', compact('logs', 'users'));
    }

    public function expenses(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end   = $request->input('end_date', now()->endOfMonth()->toDateString());

        $base = \App\Models\Expense::approvedOrPaid()->whereBetween('expense_date', [$start, $end]);

        $total = (clone $base)->sum('total_amount');
        $count = (clone $base)->count();

        $byCategory = (clone $base)
            ->selectRaw('expense_category_id, sum(total_amount) as total')
            ->with('category')
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->get();

        $byVendor = (clone $base)
            ->selectRaw('vendor_id, sum(total_amount) as total')
            ->with('vendor')
            ->groupBy('vendor_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $byMonth = \App\Models\Expense::approvedOrPaid()
            ->where('expense_date', '>=', now()->subYear()->startOfMonth())
            ->selectRaw('DATE_FORMAT(expense_date, "%Y-%m") as month, sum(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $byMethod = (clone $base)
            ->selectRaw('payment_method, sum(total_amount) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        return view('reports.expenses', compact('start', 'end', 'total', 'count', 'byCategory', 'byVendor', 'byMonth', 'byMethod'));
    }

    public function profitLoss(Request $request)
    {
        $start = $request->input('start_date', now()->startOfMonth()->toDateString());
        $end   = $request->input('end_date', now()->endOfMonth()->toDateString());

        $revenue = \App\Models\Payment::where('status', 'Success')
            ->whereBetween('paid_at', [$start, $end . ' 23:59:59'])
            ->sum('amount');

        $expensesByCategory = \App\Models\Expense::approvedOrPaid()
            ->whereBetween('expense_date', [$start, $end])
            ->selectRaw('expense_category_id, sum(total_amount) as total')
            ->with('category')
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->get();

        $totalExpenses = $expensesByCategory->sum('total');
        $netIncome = $revenue - $totalExpenses;
        $margin = $revenue > 0 ? round(($netIncome / $revenue) * 100, 1) : 0;

        // Monthly trend across this period range, grouped by month
        $monthlyRevenue = \App\Models\Payment::where('status', 'Success')
            ->whereBetween('paid_at', [$start, $end . ' 23:59:59'])
            ->selectRaw('DATE_FORMAT(paid_at, "%Y-%m") as month, sum(amount) as total')
            ->groupBy('month')->orderBy('month')->pluck('total', 'month');

        $monthlyExpenses = \App\Models\Expense::approvedOrPaid()
            ->whereBetween('expense_date', [$start, $end])
            ->selectRaw('DATE_FORMAT(expense_date, "%Y-%m") as month, sum(total_amount) as total')
            ->groupBy('month')->orderBy('month')->pluck('total', 'month');

        $months = collect(array_merge($monthlyRevenue->keys()->all(), $monthlyExpenses->keys()->all()))->unique()->sort()->values();

        return view('reports.profit_loss', compact(
            'start', 'end', 'revenue', 'expensesByCategory', 'totalExpenses',
            'netIncome', 'margin', 'months', 'monthlyRevenue', 'monthlyExpenses'
        ));
    }

    public function budgetVariance(Request $request)
    {
        $year  = (int) $request->input('year', date('Y'));
        $month = $request->filled('month') ? (int) $request->month : (int) date('n');

        $budgets = \App\Models\ExpenseBudget::with('category')
            ->where('year', $year)
            ->where(function ($q) use ($month) {
                $q->where('period', 'Monthly')->where('month', $month)
                  ->orWhere('period', 'Quarterly')->where('quarter', (int) ceil($month / 3))
                  ->orWhere('period', 'Yearly');
            })
            ->get();

        return view('reports.budget_variance', compact('budgets', 'year', 'month'));
    }

    public function approveCash(\App\Models\Payment $payment)
    {
        if ($payment->approved_at) {
            return back()->with('error', 'Payment already approved.');
        }

        $payment->update([
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        \App\Services\AuditService::log('Approved Cash Payment', "Ref: {$payment->reference}, Amount: {$payment->amount}");

        return back()->with('success', 'Cash payment approved.');
    }
}
