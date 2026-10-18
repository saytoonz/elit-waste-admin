<x-app-layout>
    @section('header')
        Website
    @endsection

    @include('website.partials.nav')

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Newsletter subscribers</h1>
            <p class="mt-2 text-sm text-gray-700">People who signed up through a newsletter box on the website. {{ number_format($subscribers->total()) }} total.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0">
            <a href="{{ route('website.subscribers.export') }}" class="block rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Download CSV</a>
        </div>
    </div>

    <form method="GET" class="mt-6">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search email…" class="rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-primary">
    </form>

    <div class="mt-6 overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Email</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Subscribed</th>
                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($subscribers as $sub)
                    <tr>
                        <td class="py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ $sub->email }}</td>
                        <td class="px-3 py-4 text-sm text-gray-500">{{ $sub->created_at->format('M j, Y') }}</td>
                        <td class="py-4 pl-3 pr-4 text-right text-sm sm:pr-6">
                            <form method="POST" action="{{ route('website.subscribers.destroy', $sub) }}" onsubmit="return confirm('Remove this subscriber?')">@csrf @method('DELETE')
                                <button class="font-semibold text-red-600">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-3 py-10 text-center text-sm text-gray-500">No subscribers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $subscribers->links() }}</div>
</x-app-layout>
