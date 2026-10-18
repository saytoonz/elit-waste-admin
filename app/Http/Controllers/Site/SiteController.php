<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Website\WebsitePage;
use App\Models\Website\WebsitePost;

class SiteController extends Controller
{
    public function home()
    {
        // rescue(): before the website migration has run there is no table yet
        $page = rescue(fn () => WebsitePage::where('is_home', true)->first(), null, false);

        // No published homepage yet → keep the old behaviour (straight to login)
        if (!$page || !$this->canView($page->status)) {
            return redirect()->route('login');
        }

        return $this->render($page);
    }

    public function page(string $slug)
    {
        $page = WebsitePage::where('slug', $slug)->firstOrFail();
        abort_unless($this->canView($page->status), 404);

        if ($page->is_home) {
            return redirect()->route('site.home', [], 301);
        }

        return $this->render($page);
    }

    public function post(string $slug)
    {
        $post = WebsitePost::where('slug', $slug)->firstOrFail();
        $isLive = $post->status === 'Published' && (!$post->published_at || $post->published_at->lte(now()));
        abort_unless($isLive || $this->isEditor(), 404);

        $related = WebsitePost::published()
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('site.post', compact('post', 'related', 'isLive'));
    }

    protected function render(WebsitePage $page)
    {
        $sections = $page->visibleSections()->get();
        return view('site.page', compact('page', 'sections'));
    }

    protected function canView(string $status): bool
    {
        return $status === 'Published' || $this->isEditor();
    }

    /** Editors can preview drafts while logged in. */
    protected function isEditor(): bool
    {
        return (bool) auth()->user()?->hasAnyRole(['Owner', 'Admin', 'SuperAdmin']);
    }
}
