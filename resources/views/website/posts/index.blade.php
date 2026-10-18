<x-app-layout>
    @section('header')
        Website
    @endsection

    @include('website.partials.nav')

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">News & blog</h1>
            <p class="mt-2 text-sm text-gray-700">Articles, tips and announcements shown on the News page and in "Latest news" sections.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('website.posts.create') }}" class="block rounded-md bg-primary px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-secondary">New post</a>
        </div>
    </div>

    <form method="GET" class="mt-6 flex flex-wrap gap-3">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search titles…" class="rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-primary">
        <select name="status" class="rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-primary">
            <option value="">All statuses</option>
            @foreach(['Published', 'Draft'] as $st)<option @selected(request('status') === $st)>{{ $st }}</option>@endforeach
        </select>
        <button class="rounded-md bg-gray-800 px-3 py-1.5 text-sm font-semibold text-white">Filter</button>
    </form>

    <div class="mt-6 overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Post</th>
                    <th class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 md:table-cell">Category</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">Date</th>
                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($posts as $post)
                    <tr>
                        <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-14 shrink-0 overflow-hidden rounded bg-gray-100">
                                    @if($post->cover_image)<img src="{{ \App\Support\Website::image($post->cover_image) }}" class="h-full w-full object-cover" alt="">@endif
                                </div>
                                <a href="{{ route('website.posts.edit', $post) }}" class="font-semibold text-gray-900 hover:text-primary">{{ $post->title }}</a>
                            </div>
                        </td>
                        <td class="hidden px-3 py-4 text-sm text-gray-500 md:table-cell">{{ $post->category ?: '—' }}</td>
                        <td class="px-3 py-4 text-sm">
                            @if($post->status === 'Published' && $post->published_at?->isFuture())
                                <span class="inline-flex rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">Scheduled</span>
                            @elseif($post->status === 'Published')
                                <span class="inline-flex rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Published</span>
                            @else
                                <span class="inline-flex rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/20">Draft</span>
                            @endif
                        </td>
                        <td class="hidden px-3 py-4 text-sm text-gray-500 sm:table-cell">{{ optional($post->published_at)->format('M j, Y') ?? '—' }}</td>
                        <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <a href="{{ $post->url }}" target="_blank" class="text-gray-500 hover:text-gray-900">View</a>
                            <a href="{{ route('website.posts.edit', $post) }}" class="ml-4 text-primary hover:text-secondary">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-10 text-center text-sm text-gray-500">No posts yet. <a href="{{ route('website.posts.create') }}" class="font-semibold text-primary">Write one</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
</x-app-layout>
