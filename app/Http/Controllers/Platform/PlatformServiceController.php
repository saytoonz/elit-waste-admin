<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\PlatformService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlatformServiceController extends Controller
{
    public function index()
    {
        $services = PlatformService::withCount('subscriptions')->orderBy('sort_order')->orderBy('name')->paginate(30);
        return view('platform.services.index', compact('services'));
    }

    public function create()
    {
        return view('platform.services.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        if (PlatformService::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] .= '-' . Str::random(4);
        }
        $validated['features'] = $this->parseFeatures($request->input('features_raw'));

        $service = PlatformService::create($validated);
        AuditService::log('Created Platform Service', "{$service->name} ({$service->currency} {$service->unit_price}/{$service->billing_cycle})");
        return redirect()->route('platform.services.index')->with('success', 'Service created.');
    }

    public function edit(PlatformService $service)
    {
        return view('platform.services.edit', compact('service'));
    }

    public function update(Request $request, PlatformService $service)
    {
        $validated = $this->validatePayload($request, $service->id);
        $validated['slug'] = $validated['slug'] ?: $service->slug;
        $validated['features'] = $this->parseFeatures($request->input('features_raw'));
        $service->update($validated);
        AuditService::log('Updated Platform Service', $service->name);
        return redirect()->route('platform.services.index')->with('success', 'Service updated.');
    }

    public function destroy(PlatformService $service)
    {
        if ($service->subscriptions()->exists()) {
            return back()->with('error', 'Cannot delete: service has active subscriptions. Deactivate instead.');
        }
        $name = $service->name;
        $service->delete();
        AuditService::log('Deleted Platform Service', $name);
        return back()->with('success', 'Service deleted.');
    }

    private function validatePayload(Request $request, ?int $id = null): array
    {
        $slugUnique = 'unique:platform_services,slug' . ($id ? ",{$id}" : '');
        return $request->validate([
            'name'              => 'required|string|max:120',
            'slug'              => "nullable|string|max:120|{$slugUnique}",
            'type'              => 'required|in:Email,Hosting,Domain,SMS,Storage,Other',
            'unit_price'        => 'required|numeric|min:0',
            'currency'          => 'required|string|max:5',
            'billing_cycle'     => 'required|in:Monthly,Quarterly,Yearly',
            'is_quantity_based' => 'sometimes|boolean',
            'unit_label'        => 'nullable|string|max:60',
            'default_quantity'  => 'nullable|integer|min:1',
            'min_quantity'      => 'nullable|integer|min:1',
            'grace_days'        => 'required|integer|min:0|max:90',
            'description'       => 'nullable|string|max:1000',
            'customer_addable'  => 'sometimes|boolean',
            'is_active'         => 'sometimes|boolean',
            'sort_order'        => 'nullable|integer',
        ]);
    }

    private function parseFeatures(?string $raw): array
    {
        if (!$raw) return [];
        return collect(preg_split('/\r\n|\r|\n/', $raw))->map(fn($l) => trim($l))->filter()->values()->all();
    }
}
