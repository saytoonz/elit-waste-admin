<x-app-layout>
    @section('header') Edit Subscription @endsection
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('platform.subscriptions.update', $subscription) }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            @csrf @method('PUT')
            @include('platform.subscriptions._form')
        </form>
    </div>
</x-app-layout>
