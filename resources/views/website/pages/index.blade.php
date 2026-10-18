<x-app-layout>
    @section('header')
        Website
    @endsection

    @include('website.partials.nav')

    @if($newLeads)
        <a href="{{ route('website.leads.index', ['status' => 'New']) }}" class="mb-6 flex items-center justify-between gap-4 rounded-lg bg-accent-orange/10 px-5 py-4 ring-1 ring-accent-orange/30 hover:bg-accent-orange/15">
            <span class="text-sm font-semibold text-gray-900">You have {{ $newLeads }} new {{ Str::plural('enquiry', $newLeads) }} from the website.</span>
            <span class="text-sm font-semibold text-accent-orange">Open leads &rarr;</span>
        </a>
    @endif

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Pages</h1>
            <p class="mt-2 text-sm text-gray-700">Every page on your public website. Open a page to edit its text, images and sections.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('website.pages.create') }}" class="block rounded-md bg-primary px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-secondary">New page</a>
        </div>
    </div>

    <div class="mt-8 overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Page</th>
                    <th class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 md:table-cell">Address</th>
                    <th class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 sm:table-cell">Sections</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="hidden px-3 py-3.5 text-left text-sm font-semibold text-gray-900 lg:table-cell">Updated</th>
                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($pages as $page)
                    <tr>
                        <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <a href="{{ route('website.pages.edit', $page) }}" class="font-semibold text-gray-900 hover:text-primary">{{ $page->title }}</a>
                            @if($page->is_home)<span class="ml-2 rounded-md bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary">Homepage</span>@endif
                        </td>
                        <td class="hidden px-3 py-4 text-sm text-gray-500 md:table-cell">/{{ $page->is_home ? '' : $page->slug }}</td>
                        <td class="hidden px-3 py-4 text-sm text-gray-500 sm:table-cell">{{ $page->sections_count }}</td>
                        <td class="px-3 py-4 text-sm">
                            @if($page->status === 'Published')
                                <span class="inline-flex rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Published</span>
                            @else
                                <span class="inline-flex rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/20">Draft</span>
                            @endif
                        </td>
                        <td class="hidden px-3 py-4 text-sm text-gray-500 lg:table-cell">{{ $page->updated_at->diffForHumans() }}</td>
                        <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <a href="{{ $page->url }}" target="_blank" class="text-gray-500 hover:text-gray-900">View</a>
                            <a href="{{ route('website.pages.edit', $page) }}" class="ml-4 text-primary hover:text-secondary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
