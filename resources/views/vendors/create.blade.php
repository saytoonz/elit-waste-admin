<x-app-layout>
    @section('header') New Vendor @endsection
    <div class="max-w-3xl mx-auto">
        <form method="POST" action="{{ route('vendors.store') }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            @csrf
            @include('vendors._form')
        </form>
    </div>
</x-app-layout>
