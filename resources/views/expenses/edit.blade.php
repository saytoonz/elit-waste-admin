<x-app-layout>
    @section('header')
        Edit Expense #{{ $expense->expense_number }}
    @endsection

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            @csrf
            @method('PUT')
            @include('expenses._form')
        </form>
    </div>
</x-app-layout>
