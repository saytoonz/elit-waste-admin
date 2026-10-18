<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Website\WebsiteLead;
use App\Models\Website\WebsiteSubscriber;
use App\Services\MyCSMSService;
use App\Support\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SiteFormController extends Controller
{
    public function submit(Request $request)
    {
        // Honeypot: real visitors never see or fill this field
        if ($request->filled('website')) {
            return back()->with('form_success', Website::setting('form_success'))->with('form_type', $request->input('form_type'));
        }

        $validated = $request->validate([
            'form_type'        => 'required|in:' . implode(',', array_keys(config('website.form_types'))),
            'name'             => 'required|string|max:120',
            'phone'            => 'required_without:email|nullable|string|max:30',
            'email'            => 'required_without:phone|nullable|email|max:150',
            'company'          => 'nullable|string|max:150',
            'location'         => 'nullable|string|max:255',
            'service_interest' => 'nullable|string|max:120',
            'frequency'        => 'nullable|string|max:60',
            'message'          => 'nullable|string|max:3000',
        ], [
            'phone.required_without' => 'Please give us a phone number or an email address.',
            'email.required_without' => 'Please give us a phone number or an email address.',
        ]);

        $lead = WebsiteLead::create($validated + [
            'type'       => $validated['form_type'],
            'status'     => 'New',
            'source_url' => Str::limit((string) url()->previous(), 250, ''),
            'ip'         => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 490, ''),
        ]);

        $this->notify($lead);

        return back()->with('form_success', Website::setting('form_success'))->with('form_type', $lead->type);
    }

    public function subscribe(Request $request)
    {
        if ($request->filled('website')) {
            return back()->with('subscribe_success', true);
        }

        $data = $request->validate(['email' => 'required|email|max:150']);

        WebsiteSubscriber::updateOrCreate(
            ['email' => strtolower($data['email'])],
            ['is_active' => true, 'ip' => $request->ip()]
        );

        return back()->with('subscribe_success', true);
    }

    protected function notify(WebsiteLead $lead): void
    {
        $phone = Website::setting('lead_notify_phone');
        if (!$phone) return;

        try {
            $message = "New website {$lead->type_label}: {$lead->name}"
                . ($lead->phone ? ", {$lead->phone}" : '')
                . ($lead->location ? " ({$lead->location})" : '');
            app(MyCSMSService::class)->send($phone, Str::limit($message, 300));
        } catch (\Throwable $e) {
            Log::warning('Lead SMS alert failed: ' . $e->getMessage());
        }
    }
}
