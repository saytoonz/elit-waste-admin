<x-app-layout>
    @section('header') Edit Category @endsection
    <div class="max-w-3xl mx-auto">
        <form method="POST" action="{{ route('expense_categories.update', $category) }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            @csrf @method('PUT')
            @include('expense_categories._form')
        </form>
    </div>
</x-app-layout>
