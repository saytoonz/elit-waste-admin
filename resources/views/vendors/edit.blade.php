<x-app-layout>
    @section('header') Edit Vendor @endsection
    <div class="max-w-3xl mx-auto">
        <form method="POST" action="{{ route('vendors.update', $vendor) }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            @csrf @method('PUT')
            @include('vendors._form')
        </form>
    </div>
</x-app-layout>
