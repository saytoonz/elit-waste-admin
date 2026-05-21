<x-app-layout>
    @section('header') Edit Platform Service @endsection
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('platform.services.update', $service) }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            @csrf @method('PUT')
            @include('platform.services._form')
        </form>
    </div>
</x-app-layout>
