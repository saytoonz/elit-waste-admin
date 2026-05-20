<x-app-layout>
    @section('header')
        Reports & Analytics
    @endsection

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('reports.profit_loss') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-emerald-500">
            <h3 class="text-lg font-semibold text-gray-900">Profit & Loss</h3>
            <p class="mt-2 text-sm text-gray-600">Revenue vs Expenses with net income and margin for any period.</p>
        </a>

        <a href="{{ route('reports.expenses') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-red-500">
            <h3 class="text-lg font-semibold text-gray-900">Expense Summary</h3>
            <p class="mt-2 text-sm text-gray-600">Breakdown by category, vendor, method, and month.</p>
        </a>

        <a href="{{ route('reports.budget_variance') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-purple-500">
            <h3 class="text-lg font-semibold text-gray-900">Budget Variance</h3>
            <p class="mt-2 text-sm text-gray-600">Track actual spending against budget targets per category.</p>
        </a>

        <a href="{{ route('reports.receivables') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-amber-500">
            <h3 class="text-lg font-semibold text-gray-900">Aged Receivables</h3>
            <p class="mt-2 text-sm text-gray-600">Customers with outstanding balances, sorted by highest debt.</p>
        </a>

        <a href="{{ route('reports.revenue') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-green-500">
            <h3 class="text-lg font-semibold text-gray-900">Revenue & Income</h3>
            <p class="mt-2 text-sm text-gray-600">Income over time. Cash vs Online payments.</p>
        </a>

        <a href="{{ route('reports.payments') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-blue-500">
            <h3 class="text-lg font-semibold text-gray-900">Payment History</h3>
            <p class="mt-2 text-sm text-gray-600">All transactions with filters and export.</p>
        </a>

        <a href="{{ route('reports.cash.pending') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-yellow-500">
            <h3 class="text-lg font-semibold text-gray-900">Cash Approvals</h3>
            <p class="mt-2 text-sm text-gray-600">Review and confirm cash payments collected by agents.</p>
        </a>

        <a href="{{ route('reports.audit') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900">System Audit Logs</h3>
            <p class="mt-2 text-sm text-gray-600">Track user activity, system changes, and security events.</p>
        </a>
    </div>
</x-app-layout>
