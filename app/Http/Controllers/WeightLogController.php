<?php

namespace App\Http\Controllers;

use App\Models\WeightLog;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WeightLogController extends Controller
{
    /**
     * Display a listing of weighing records with filters.
     */
    public function index(Request $request)
    {
        $query = WeightLog::query()->with('recordedBy');

        if ($request->filled('search')) {
            $query->where('vehicle_label', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('trash_type')) {
            $query->where('trash_type', $request->trash_type);
        }

        if ($request->filled('from')) {
            $query->where('logged_at', '>=', Carbon::parse($request->from)->startOfDay());
        }

        if ($request->filled('to')) {
            $query->where('logged_at', '<=', Carbon::parse($request->to)->endOfDay());
        }

        // Range totals for the summary strip (respects the active filters).
        $rangeTotalNet = (clone $query)->sum('net_weight');
        $rangeCount    = (clone $query)->count();

        $weightLogs = $query->latest('logged_at')->paginate(15)->withQueryString();
        $trashTypes = WeightLog::TRASH_TYPES;

        return view('weight-logs.index', compact('weightLogs', 'trashTypes', 'rangeTotalNet', 'rangeCount'));
    }

    /**
     * Show the form for creating a new weighing record.
     */
    public function create()
    {
        $trashTypes = WeightLog::TRASH_TYPES;
        return view('weight-logs.create', compact('trashTypes'));
    }

    /**
     * Store a newly created weighing record.
     */
    public function store(Request $request)
    {
        $validated = $this->validateData($request);
        $validated['recorded_by'] = auth()->id();

        $log = WeightLog::create($validated);

        AuditService::log('Created Weight Log', "Vehicle: {$log->vehicle_label}, Net: {$log->net_weight}kg ({$log->trash_type})");

        return redirect()->route('weight-logs.index')
            ->with('success', 'Weighing record added successfully.');
    }

    /**
     * Show the form for editing a weighing record.
     */
    public function edit(WeightLog $weightLog)
    {
        $trashTypes = WeightLog::TRASH_TYPES;
        return view('weight-logs.edit', compact('weightLog', 'trashTypes'));
    }

    /**
     * Update the specified weighing record.
     */
    public function update(Request $request, WeightLog $weightLog)
    {
        $validated = $this->validateData($request);

        $weightLog->update($validated);

        AuditService::log('Updated Weight Log', "ID: {$weightLog->id} Vehicle: {$weightLog->vehicle_label}, Net: {$weightLog->net_weight}kg");

        return redirect()->route('weight-logs.index')
            ->with('success', 'Weighing record updated successfully.');
    }

    /**
     * Remove the specified weighing record.
     */
    public function destroy(WeightLog $weightLog)
    {
        $weightLog->delete();

        AuditService::log('Deleted Weight Log', "ID: {$weightLog->id} Vehicle: {$weightLog->vehicle_label}");

        return redirect()->route('weight-logs.index')
            ->with('success', 'Weighing record deleted successfully.');
    }

    /**
     * Weight analytics: net weight over time plus breakdowns.
     */
    public function analytics(Request $request)
    {
        $granularities = ['weekly', 'monthly', 'quarterly', 'half_yearly', 'yearly'];
        $granularity = in_array($request->granularity, $granularities, true)
            ? $request->granularity
            : 'monthly';

        // Resolve the reporting window.
        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        if ($request->filled('from')) {
            $from = Carbon::parse($request->from)->startOfDay();
        } else {
            $from = match ($granularity) {
                'weekly'      => now()->subWeeks(11),
                'quarterly'   => now()->subQuarters(7),
                'half_yearly' => now()->subMonths(30),
                'yearly'      => now()->subYears(4),
                default       => now()->subMonths(11),
            };
            $from = $from->startOfDay();
        }

        // MySQL period key per granularity (mirrors DashboardController's raw-SQL approach).
        $periodExpr = match ($granularity) {
            'weekly'      => 'YEARWEEK(logged_at, 3)',
            'quarterly'   => "CONCAT(YEAR(logged_at), '-Q', QUARTER(logged_at))",
            'half_yearly' => "CONCAT(YEAR(logged_at), '-H', IF(MONTH(logged_at) <= 6, 1, 2))",
            'yearly'      => 'YEAR(logged_at)',
            default       => "DATE_FORMAT(logged_at, '%Y-%m')",
        };

        $rows = WeightLog::selectRaw("$periodExpr as period, SUM(net_weight) as total")
            ->whereBetween('logged_at', [$from, $to])
            ->groupBy('period')
            ->pluck('total', 'period');

        $lookup = [];
        foreach ($rows as $key => $value) {
            $lookup[(string) $key] = (float) $value;
        }

        $buckets = $this->buildBuckets($granularity, $from->copy(), $to->copy());
        $seriesLabels = array_map(fn ($b) => $b['label'], $buckets);
        $seriesValues = array_map(fn ($b) => $lookup[(string) $b['key']] ?? 0, $buckets);

        // Breakdown by trash type within the window.
        $byType = WeightLog::selectRaw('trash_type, SUM(net_weight) as total, COUNT(*) as cnt')
            ->whereBetween('logged_at', [$from, $to])
            ->groupBy('trash_type')
            ->orderByDesc('total')
            ->get();

        // Breakdown by vehicle (top 10) within the window.
        $byVehicle = WeightLog::selectRaw('vehicle_label, SUM(net_weight) as total, COUNT(*) as cnt')
            ->whereBetween('logged_at', [$from, $to])
            ->groupBy('vehicle_label')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // KPIs.
        $totalNet = (float) $byType->sum('total');
        $count    = (int) $byType->sum('cnt');
        $avgNet   = $count > 0 ? $totalNet / $count : 0;
        $heaviestType = $byType->first();

        $typeColors = [
            'General'    => '#6B7280',
            'Organic'    => '#16A34A',
            'Recyclable' => '#2563EB',
            'Hazardous'  => '#DC2626',
        ];

        // JSON payloads for Chart.js.
        $chartLabels    = json_encode($seriesLabels);
        $chartValues    = json_encode($seriesValues);
        $typeLabels     = $byType->pluck('trash_type')->toJson();
        $typeValues     = $byType->pluck('total')->map(fn ($t) => (float) $t)->toJson();
        $typeColorsJson = $byType->pluck('trash_type')->map(fn ($t) => $typeColors[$t] ?? '#6B7280')->toJson();
        $vehicleLabels  = $byVehicle->pluck('vehicle_label')->toJson();
        $vehicleValues  = $byVehicle->pluck('total')->map(fn ($t) => (float) $t)->toJson();

        return view('weight-logs.analytics', compact(
            'granularity',
            'from',
            'to',
            'byType',
            'byVehicle',
            'totalNet',
            'count',
            'avgNet',
            'heaviestType',
            'typeColors',
            'chartLabels',
            'chartValues',
            'typeLabels',
            'typeValues',
            'typeColorsJson',
            'vehicleLabels',
            'vehicleValues'
        ));
    }

    /**
     * Shared validation for store/update. Net weight is computed by the model.
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'logged_at'     => 'required|date',
            'vehicle_label' => 'required|string|max:255',
            'trash_type'    => ['required', Rule::in(WeightLog::TRASH_TYPES)],
            'gross_weight'  => 'required|numeric|min:0',
            'tare_weight'   => 'required|numeric|min:0|lte:gross_weight',
            'notes'         => 'nullable|string',
        ]);
    }

    /**
     * Build ordered, gap-free period buckets so empty periods render as zero.
     * Keys match the SQL period expression used in analytics().
     */
    private function buildBuckets(string $granularity, Carbon $from, Carbon $to): array
    {
        $buckets = [];

        switch ($granularity) {
            case 'weekly':
                $cursor = $from->copy()->startOfWeek(Carbon::MONDAY);
                while ($cursor <= $to) {
                    $buckets[] = [
                        'key'   => (string) ($cursor->isoWeekYear * 100 + $cursor->isoWeek),
                        'label' => $cursor->format('d M'),
                    ];
                    $cursor->addWeek();
                }
                break;

            case 'quarterly':
                $cursor = $from->copy()->startOfQuarter();
                while ($cursor <= $to) {
                    $buckets[] = [
                        'key'   => $cursor->year . '-Q' . $cursor->quarter,
                        'label' => 'Q' . $cursor->quarter . ' ' . $cursor->year,
                    ];
                    $cursor->addQuarter();
                }
                break;

            case 'half_yearly':
                $cursor = $from->copy();
                $cursor->month($cursor->month <= 6 ? 1 : 7)->startOfMonth();
                while ($cursor <= $to) {
                    $half = $cursor->month <= 6 ? 1 : 2;
                    $buckets[] = [
                        'key'   => $cursor->year . '-H' . $half,
                        'label' => 'H' . $half . ' ' . $cursor->year,
                    ];
                    $cursor->addMonths(6);
                }
                break;

            case 'yearly':
                $cursor = $from->copy()->startOfYear();
                while ($cursor <= $to) {
                    $buckets[] = [
                        'key'   => (string) $cursor->year,
                        'label' => (string) $cursor->year,
                    ];
                    $cursor->addYear();
                }
                break;

            case 'monthly':
            default:
                $cursor = $from->copy()->startOfMonth();
                while ($cursor <= $to) {
                    $buckets[] = [
                        'key'   => $cursor->format('Y-m'),
                        'label' => $cursor->format('M Y'),
                    ];
                    $cursor->addMonth();
                }
                break;
        }

        return $buckets;
    }
}
