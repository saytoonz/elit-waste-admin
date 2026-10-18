<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Website\WebsitePage;
use App\Models\Website\WebsiteSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    public function store(Request $request, WebsitePage $page)
    {
        $request->validate(['type' => ['required', Rule::in(array_keys(config('website.sections')))]]);

        $fields = config("website.sections.{$request->type}.fields");
        $section = $page->sections()->create([
            'type'       => $request->type,
            'data'       => ContentSanitizer::clean($fields, []),
            'sort_order' => (int) $page->sections()->max('sort_order') + 1,
        ]);

        return redirect()->route('website.sections.edit', $section)->with('success', 'Section added — fill in the content below.');
    }

    public function edit(WebsiteSection $section)
    {
        $section->load('page');
        return view('website.sections.edit', [
            'section'    => $section,
            'definition' => $section->definition(),
        ]);
    }

    public function update(Request $request, WebsiteSection $section)
    {
        $request->validate(['data' => 'required|json']);

        $section->update([
            'data' => ContentSanitizer::clean($section->definition()['fields'], json_decode($request->data, true)),
            'is_visible' => $request->boolean('is_visible'),
        ]);
        $section->page->touch();

        if ($request->input('after') === 'page') {
            return redirect()->route('website.pages.edit', $section->page)->with('success', 'Section saved.');
        }

        return back()->with('success', 'Section saved.');
    }

    public function destroy(WebsiteSection $section)
    {
        $page = $section->page;
        $section->delete();

        return redirect()->route('website.pages.edit', $page)->with('success', 'Section removed.');
    }

    public function toggle(WebsiteSection $section)
    {
        $section->update(['is_visible' => !$section->is_visible]);
        return back()->with('success', $section->is_visible ? 'Section is now visible.' : 'Section hidden from the website.');
    }

    public function duplicate(WebsiteSection $section)
    {
        DB::transaction(function () use ($section) {
            WebsiteSection::where('website_page_id', $section->website_page_id)
                ->where('sort_order', '>', $section->sort_order)
                ->increment('sort_order');
            $copy = $section->replicate();
            $copy->sort_order = $section->sort_order + 1;
            $copy->save();
        });

        return back()->with('success', 'Section duplicated.');
    }

    /** Move a section one place up or down. */
    public function move(Request $request, WebsiteSection $section)
    {
        $direction = $request->input('direction') === 'up' ? 'up' : 'down';
        $ids = $section->page->sections()->pluck('id')->all();
        $pos = array_search($section->id, $ids, true);
        $swap = $direction === 'up' ? $pos - 1 : $pos + 1;

        if ($pos !== false && isset($ids[$swap])) {
            [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];
            $this->saveOrder($ids);
        }

        return back();
    }

    protected function saveOrder(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach ($ids as $i => $id) {
                WebsiteSection::whereKey($id)->update(['sort_order' => $i + 1]);
            }
        });
    }
}
