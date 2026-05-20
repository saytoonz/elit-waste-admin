<x-app-layout>
    @section('header')
        Record Expense
    @endsection

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            @csrf
            @include('expenses._form')
        </form>
    </div>
</x-app-layout>
