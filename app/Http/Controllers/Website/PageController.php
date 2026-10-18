<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Website\WebsiteLead;
use App\Models\Website\WebsitePage;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index()
    {
        $pages = WebsitePage::withCount('sections')->orderByDesc('is_home')->orderBy('title')->get();
        $newLeads = WebsiteLead::where('status', 'New')->count();

        return view('website.pages.index', compact('pages', 'newLeads'));
    }

    public function create()
    {
        return view('website.pages.create', ['page' => new WebsitePage(['status' => 'Draft'])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['updated_by'] = auth()->id();
        $page = WebsitePage::create($data);

        // Start every new page with a header so it is never blank
        $page->sections()->create([
            'type' => 'page_header',
            'data' => ['heading' => $page->title, 'subheading' => null, 'eyebrow' => null, 'image' => null],
            'sort_order' => 1,
        ]);

        AuditService::log('Website Page Created', $page->title);

        return redirect()->route('website.pages.edit', $page)->with('success', 'Page created. Now add sections to build it.');
    }

    public function edit(WebsitePage $page)
    {
        $page->load('sections');
        return view('website.pages.edit', compact('page'));
    }

    public function update(Request $request, WebsitePage $page)
    {
        $data = $this->validated($request, $page);
        $data['updated_by'] = auth()->id();
        $page->update($data);

        AuditService::log('Website Page Updated', $page->title);

        return back()->with('success', 'Page settings saved.');
    }

    public function destroy(WebsitePage $page)
    {
        if ($page->is_home) {
            return back()->with('error', 'Set another page as the homepage before deleting this one.');
        }

        $page->delete();
        AuditService::log('Website Page Deleted', $page->title);

        return redirect()->route('website.pages.index')->with('success', 'Page deleted.');
    }

    public function duplicate(WebsitePage $page)
    {
        $copy = DB::transaction(function () use ($page) {
            $slug = $this->uniqueSlug($page->slug . '-copy');
            $copy = $page->replicate(['is_home']);
            $copy->fill(['title' => $page->title . ' (copy)', 'slug' => $slug, 'status' => 'Draft', 'is_home' => false, 'updated_by' => auth()->id()]);
            $copy->save();
            foreach ($page->sections as $section) {
                $copy->sections()->create($section->only(['type', 'data', 'is_visible', 'sort_order']));
            }
            return $copy;
        });

        return redirect()->route('website.pages.edit', $copy)->with('success', 'Page duplicated as a draft.');
    }

    public function makeHome(WebsitePage $page)
    {
        DB::transaction(function () use ($page) {
            WebsitePage::where('is_home', true)->update(['is_home' => false]);
            $page->update(['is_home' => true, 'status' => 'Published']);
        });

        AuditService::log('Website Homepage Changed', $page->title);

        return back()->with('success', "\"{$page->title}\" is now the homepage.");
    }

    protected function validated(Request $request, ?WebsitePage $page = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title'))]);

        $data = $request->validate([
            'title'            => 'required|string|max:150',
            'slug'             => [
                'required', 'string', 'max:150', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('website_pages', 'slug')->ignore($page?->id),
                Rule::notIn(config('website.reserved_slugs')),
            ],
            'status'           => 'required|in:Draft,Published',
            'meta_title'       => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:500',
            'og_image'         => 'nullable|string|max:255',
        ], [
            'slug.not_in' => 'That web address is used by the dashboard. Please choose another.',
        ]);

        if ($page?->is_home && $data['status'] !== 'Published') {
            $data['status'] = 'Published'; // the homepage can never be hidden
        }

        return $data;
    }

    protected function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 2;
        while (WebsitePage::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }
}
