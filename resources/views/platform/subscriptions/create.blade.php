<x-app-layout>
    @section('header') New Subscription @endsection
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('platform.subscriptions.store') }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            @csrf
            @include('platform.subscriptions._form')
        </form>
    </div>
</x-app-layout>
