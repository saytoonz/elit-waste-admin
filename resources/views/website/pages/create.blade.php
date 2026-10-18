<x-app-layout>
    @section('header')
        Website
    @endsection

    @include('website.partials.nav')

    <div class="mx-auto max-w-2xl">
        <h1 class="text-base font-semibold text-gray-900">New page</h1>
        <p class="mt-1 text-sm text-gray-600">Give the page a title. You'll add sections (text, photos, forms…) on the next screen.</p>
        <form method="POST" action="{{ route('website.pages.store') }}" class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
            @csrf
            @include('website.pages.settings-fields')
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('website.pages.index') }}" class="px-3 py-2 text-sm font-semibold text-gray-900">Cancel</a>
                <button class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary">Create page</button>
            </div>
        </form>
    </div>

    @include('website.partials.editor-scripts')
</x-app-layout>
