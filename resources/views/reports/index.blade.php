<x-app-layout>
    @section('header')
        Reports & Analytics
    @endsection

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Receivables Report -->
        <a href="{{ route('reports.receivables') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-red-500">
            <h3 class="text-lg font-semibold text-gray-900">Aged Receivables</h3>
            <p class="mt-2 text-sm text-gray-600">View customers with outstanding balances, sorted by highest debt. Filter by zone.</p>
        </a>

        <!-- Revenue Report -->
        <a href="{{ route('reports.revenue') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-green-500">
            <h3 class="text-lg font-semibold text-gray-900">Revenue & Income</h3>
            <p class="mt-2 text-sm text-gray-600">Breakdown of income over time. Compare Cash vs Online payments.</p>
        </a>

        <!-- Payment History -->
        <a href="{{ route('reports.payments') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-blue-500">
            <h3 class="text-lg font-semibold text-gray-900">Payment History</h3>
            <p class="mt-2 text-sm text-gray-600">Comprehensive list of all transactions with search and export options.</p>
        </a>

        <!-- Audit Logs -->
        <a href="{{ route('reports.audit') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900">System Audit Logs</h3>
            <p class="mt-2 text-sm text-gray-600">Track user activity, system changes, and security events.</p>
        </a>

        <!-- Cash Approvals -->
        <a href="{{ route('reports.cash.pending') }}" class="block p-6 bg-white rounded-lg shadow hover:bg-gray-50 transition border-l-4 border-yellow-500">
            <h3 class="text-lg font-semibold text-gray-900">Cash Approvals</h3>
            <p class="mt-2 text-sm text-gray-600">Review and confirm cash payments collected by agents.</p>
        </a>
    </div>
</x-app-layout>
