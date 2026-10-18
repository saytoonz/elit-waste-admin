<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Website\WebsitePost;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = WebsitePost::with('author')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('published_at')->latest('id')
            ->paginate(20)->withQueryString();

        return view('website.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('website.posts.form', ['post' => new WebsitePost(['status' => 'Draft', 'published_at' => now()])]);
    }

    public function store(Request $request)
    {
        $post = WebsitePost::create($this->validated($request) + ['author_id' => auth()->id()]);
        AuditService::log('Website Post Created', $post->title);

        return redirect()->route('website.posts.edit', $post)->with('success', 'Post saved.');
    }

    public function edit(WebsitePost $post)
    {
        return view('website.posts.form', compact('post'));
    }

    public function update(Request $request, WebsitePost $post)
    {
        $post->update($this->validated($request, $post));
        AuditService::log('Website Post Updated', $post->title);

        return back()->with('success', 'Post saved.');
    }

    public function destroy(WebsitePost $post)
    {
        $post->delete();
        AuditService::log('Website Post Deleted', $post->title);

        return redirect()->route('website.posts.index')->with('success', 'Post deleted.');
    }

    protected function validated(Request $request, ?WebsitePost $post = null): array
    {
        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('title'))]);

        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'slug'             => ['required', 'string', 'max:200', 'regex:/^[a-z0-9-]+$/', Rule::unique('website_posts', 'slug')->ignore($post?->id)],
            'category'         => 'nullable|string|max:80',
            'excerpt'          => 'nullable|string|max:500',
            'body'             => 'nullable|string|max:100000',
            'cover_image'      => 'nullable|string|max:255',
            'status'           => 'required|in:Draft,Published',
            'published_at'     => 'nullable|date',
            'meta_title'       => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:500',
        ]);

        if ($data['status'] === 'Published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
