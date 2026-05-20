<x-app-layout>
    @section('header') Edit Recurring Expense @endsection
    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('recurring_expenses.update', $recurring) }}" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
            @csrf @method('PUT')
            @include('recurring_expenses._form')
        </form>
    </div>
</x-app-layout>
