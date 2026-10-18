<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Website\WebsiteMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $media = $this->query($request)->paginate(36)->withQueryString();
        return view('website.media.index', compact('media'));
    }

    /** JSON list for the image picker modal. */
    public function picker(Request $request)
    {
        $page = $this->query($request)->paginate(48);

        return response()->json([
            'items' => $page->getCollection()->map(fn ($m) => [
                'id' => $m->id, 'path' => $m->path, 'url' => $m->url, 'name' => $m->original_name, 'alt' => $m->alt,
            ]),
            'next' => $page->hasMorePages() ? $page->currentPage() + 1 : null,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files'   => 'required|array|max:20',
            'files.*' => 'file|mimes:jpg,jpeg,png,webp,gif|max:8192',
        ], [
            'files.*.mimes' => 'Only JPG, PNG, WEBP or GIF images can be uploaded.',
            'files.*.max'   => 'Each image must be 8MB or smaller.',
        ]);

        $created = [];
        foreach ($request->file('files') as $file) {
            $stored = $file->store('website/' . date('Y/m'), 'public');
            $size = @getimagesize($file->getRealPath()) ?: [null, null];

            $created[] = WebsiteMedia::create([
                'path'          => 'storage/' . $stored,
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 250),
                'mime'          => $file->getMimeType(),
                'size'          => $file->getSize(),
                'width'         => $size[0],
                'height'        => $size[1],
                'alt'           => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'uploaded_by'   => auth()->id(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['items' => collect($created)->map(fn ($m) => [
                'id' => $m->id, 'path' => $m->path, 'url' => $m->url, 'name' => $m->original_name, 'alt' => $m->alt,
            ])]);
        }

        return back()->with('success', count($created) . ' image(s) uploaded.');
    }

    public function update(Request $request, WebsiteMedia $media)
    {
        $media->update($request->validate(['alt' => 'nullable|string|max:255']));
        return back()->with('success', 'Image details saved.');
    }

    public function destroy(WebsiteMedia $media)
    {
        if ($media->isUploaded()) {
            Storage::disk('public')->delete(substr($media->path, strlen('storage/')));
        }
        $media->delete();

        return back()->with('success', 'Image deleted. Any section still using it will show no image until you choose a new one.');
    }

    protected function query(Request $request)
    {
        return WebsiteMedia::query()
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('original_name', 'like', '%' . $request->q . '%')
                ->orWhere('alt', 'like', '%' . $request->q . '%')))
            ->latest('id');
    }
}
