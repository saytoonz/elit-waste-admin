<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Website\WebsiteMenuItem;
use App\Models\Website\WebsitePage;
use App\Support\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public const LOCATIONS = [
        'header'   => 'Main menu (top of every page)',
        'footer_1' => 'Footer column 1',
        'footer_2' => 'Footer column 2',
        'footer_3' => 'Footer column 3',
    ];

    public function index()
    {
        $items = WebsiteMenuItem::with('page')->orderBy('sort_order')->orderBy('id')->get()->groupBy('location');
        $pages = WebsitePage::orderBy('title')->get(['id', 'title', 'status']);
        $titles = [
            'footer_1' => Website::setting('footer_col1_title'),
            'footer_2' => Website::setting('footer_col2_title'),
            'footer_3' => Website::setting('footer_col3_title'),
        ];

        return view('website.menu.index', ['items' => $items, 'pages' => $pages, 'locations' => self::LOCATIONS, 'titles' => $titles]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = (int) WebsiteMenuItem::where('location', $data['location'])->max('sort_order') + 1;
        WebsiteMenuItem::create($data);

        return back()->with('success', 'Menu link added.');
    }

    public function update(Request $request, WebsiteMenuItem $item)
    {
        $item->update($this->validated($request));
        return back()->with('success', 'Menu link saved.');
    }

    public function destroy(WebsiteMenuItem $item)
    {
        $item->delete();
        return back()->with('success', 'Menu link removed.');
    }

    public function move(Request $request, WebsiteMenuItem $item)
    {
        $ids = WebsiteMenuItem::where('location', $item->location)->orderBy('sort_order')->orderBy('id')->pluck('id')->all();
        $pos = array_search($item->id, $ids, true);
        $swap = $request->input('direction') === 'up' ? $pos - 1 : $pos + 1;

        if ($pos !== false && isset($ids[$swap])) {
            [$ids[$pos], $ids[$swap]] = [$ids[$swap], $ids[$pos]];
            DB::transaction(function () use ($ids) {
                foreach ($ids as $i => $id) {
                    WebsiteMenuItem::whereKey($id)->update(['sort_order' => $i + 1]);
                }
            });
        }

        return back();
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'location'        => 'required|in:' . implode(',', array_keys(self::LOCATIONS)),
            'label'           => 'required|string|max:60',
            'website_page_id' => 'nullable|exists:website_pages,id',
            'url'             => ['nullable', 'string', 'max:255', 'required_without:website_page_id', 'regex:/^(\/|https?:\/\/|mailto:|tel:|#)/i'],
            'new_tab'         => 'nullable|boolean',
        ], [
            'url.required_without' => 'Choose a page or type a link.',
            'url.regex'            => 'Links must start with /, http(s)://, mailto:, tel: or #.',
        ]);

        $data['new_tab'] = $request->boolean('new_tab');
        if (!empty($data['website_page_id'])) {
            $data['url'] = null;
        }

        return $data;
    }
}
