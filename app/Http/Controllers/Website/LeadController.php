<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Website\WebsiteLead;
use App\Models\Website\WebsiteLeadActivity;
use App\Services\AuditService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $leads = $this->filtered($request)->with('assignee')->latest()->paginate(25)->withQueryString();

        $counts = WebsiteLead::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $dueToday = WebsiteLead::whereNotIn('status', ['Won', 'Lost'])->whereDate('follow_up_at', '<=', today())->count();
        $thisMonth = WebsiteLead::where('created_at', '>=', now()->startOfMonth())->count();
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('website.leads.index', compact('leads', 'counts', 'dueToday', 'thisMonth', 'users'));
    }

    public function show(WebsiteLead $lead)
    {
        $lead->load(['activities.user', 'assignee', 'customer']);
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('website.leads.show', compact('lead', 'users'));
    }

    public function update(Request $request, WebsiteLead $lead)
    {
        $data = $request->validate([
            'status'       => 'required|in:' . implode(',', WebsiteLead::STATUSES),
            'assigned_to'  => 'nullable|exists:users,id',
            'follow_up_at' => 'nullable|date',
        ]);

        $changes = [];
        if ($data['status'] !== $lead->status) {
            $changes[] = "Status: {$lead->status} → {$data['status']}";
        }
        if ((int) ($data['assigned_to'] ?? 0) !== (int) $lead->assigned_to) {
            $name = $data['assigned_to'] ? User::find($data['assigned_to'])?->name : 'nobody';
            $changes[] = "Assigned to {$name}";
        }

        $lead->update($data);

        if ($changes) {
            $lead->activities()->create(['user_id' => auth()->id(), 'type' => 'status', 'body' => implode('. ', $changes)]);
        }

        return back()->with('success', 'Lead updated.');
    }

    public function addActivity(Request $request, WebsiteLead $lead)
    {
        $data = $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(WebsiteLeadActivity::TYPES)),
            'body' => 'required|string|max:3000',
        ]);

        $lead->activities()->create($data + ['user_id' => auth()->id()]);

        // Logging any contact moves a brand-new lead along automatically
        if ($lead->status === 'New' && $data['type'] !== 'note') {
            $lead->update(['status' => 'Contacted']);
        }

        return back()->with('success', 'Activity logged.');
    }

    /**
     * Pre-fill the dashboard's New Customer form with the lead's details.
     * The lead is linked once the customer is created from that form.
     */
    public function convert(WebsiteLead $lead)
    {
        session()->put('converting_lead_id', $lead->id);

        return redirect()->route('customers.create')->withInput([
            'name'    => $lead->name,
            'phone'   => $lead->phone,
            'address' => $lead->location,
            'type'    => str_starts_with((string) $lead->service_interest, 'Household') || !$lead->service_interest ? 'Residential' : 'Commercial',
            'notes'   => trim("From website {$lead->type_label}. " . ($lead->frequency ? "Wants {$lead->frequency} collection. " : '') . ($lead->message ?? '')),
        ]);
    }

    public function destroy(WebsiteLead $lead)
    {
        $lead->delete();
        AuditService::log('Website Lead Deleted', $lead->name);

        return redirect()->route('website.leads.index')->with('success', 'Lead deleted.');
    }

    public function export(Request $request)
    {
        $rows = $this->filtered($request)->with('assignee')->latest()->get();
        $filename = 'website_leads_' . date('Y-m-d_H-i') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Type', 'Status', 'Name', 'Phone', 'Email', 'Company', 'Location', 'Interest', 'Frequency', 'Message', 'Assigned to', 'Follow up']);
            foreach ($rows as $l) {
                fputcsv($out, [
                    $l->created_at->format('Y-m-d H:i'), $l->type_label, $l->status, $l->name, $l->phone, $l->email,
                    $l->company, $l->location, $l->service_interest, $l->frequency, $l->message,
                    $l->assignee?->name, optional($l->follow_up_at)->toDateString(),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function filtered(Request $request)
    {
        return WebsiteLead::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('assigned_to'), fn ($q) => $q->where('assigned_to', $request->assigned_to))
            ->when($request->boolean('due'), fn ($q) => $q->whereNotIn('status', ['Won', 'Lost'])->whereDate('follow_up_at', '<=', today()))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term)->orWhere('location', 'like', $term));
            });
    }
}
