<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Website\WebsiteSetting;
use App\Services\AuditService;
use App\Support\Website;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show()
    {
        return view('website.settings', [
            'groups' => config('website.settings'),
            'values' => WebsiteSetting::allWithDefaults(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate(['data' => 'required|json']);

        $fields = collect(config('website.settings'))->flatMap(fn ($g) => $g['fields'])->all();
        $clean = ContentSanitizer::clean($fields, json_decode($request->data, true));

        WebsiteSetting::put($clean);
        Website::flush();
        AuditService::log('Website Settings Updated');

        return back()->with('success', 'Website settings saved.');
    }
}
