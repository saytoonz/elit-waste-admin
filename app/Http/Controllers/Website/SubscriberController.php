<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Website\WebsiteSubscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $subscribers = WebsiteSubscriber::query()
            ->when($request->filled('q'), fn ($q) => $q->where('email', 'like', '%' . $request->q . '%'))
            ->latest()->paginate(50)->withQueryString();

        return view('website.subscribers.index', compact('subscribers'));
    }

    public function destroy(WebsiteSubscriber $subscriber)
    {
        $subscriber->delete();
        return back()->with('success', 'Subscriber removed.');
    }

    public function export()
    {
        $rows = WebsiteSubscriber::where('is_active', true)->orderBy('email')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Email', 'Subscribed on']);
            foreach ($rows as $s) {
                fputcsv($out, [$s->email, $s->created_at->toDateString()]);
            }
            fclose($out);
        }, 'website_subscribers_' . date('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
