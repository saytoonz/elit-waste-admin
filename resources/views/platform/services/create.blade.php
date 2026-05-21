<x-app-layout>
    @section('header') New Platform Service @endsection
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('platform.services.store') }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            @csrf
            @include('platform.services._form')
        </form>
    </div>
</x-app-layout>
