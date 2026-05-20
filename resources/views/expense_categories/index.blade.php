<x-app-layout>
    @section('header')
        Expense Categories
    @endsection

    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-base font-semibold leading-6 text-gray-900">Expense Categories</h1>
            <p class="mt-2 text-sm text-gray-700">Organize your expenses. Supports nested sub-categories.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('expense_categories.create') }}" class="block rounded-md bg-primary px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-secondary">New Category</a>
        </div>
    </div>

    @if(session('success'))<div class="mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 border border-green-200">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700 border border-red-200">{{ session('error') }}</div>@endif

    <div class="mt-6 bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-gray-700 uppercase">Name</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Code</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Parent</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Expenses</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                    <th class="relative py-3 pl-3 pr-4"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($categories as $cat)
                    <tr>
                        <td class="whitespace-nowrap py-3 pl-4 pr-3 text-sm">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full" style="background-color: {{ $cat->color }}"></span>
                                <span class="font-medium text-gray-900">{{ $cat->name }}</span>
                            </span>
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-500 font-mono">{{ $cat->code ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $cat->parent->name ?? '—' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-700">{{ $cat->expenses_count }}</td>
                        <td class="px-3 py-3 text-sm">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $cat->is_active ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-gray-50 text-gray-700 ring-gray-600/20' }}">
                                {{ $cat->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap py-3 pl-3 pr-4 text-right text-sm font-medium flex justify-end gap-2">
                            <a href="{{ route('expense_categories.edit', $cat) }}" class="text-primary hover:underline">Edit</a>
                            <form action="{{ route('expense_categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete category?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500">No categories yet. <a href="{{ route('expense_categories.create') }}" class="text-primary font-bold hover:underline">Create one</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $categories->links() }}</div>
</x-app-layout>
