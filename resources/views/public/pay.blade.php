<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Invoice - Elite Waste</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md overflow-hidden">
        <div class="bg-indigo-600 p-6 text-center">
            <h1 class="text-white text-xl font-bold">Elite Waste Management</h1>
            <p class="text-indigo-100 text-sm mt-1">Secure Payment Portal</p>
        </div>
        
        <div class="p-6">
            <div class="text-center mb-6">
                <p class="text-gray-500 text-sm">Invoice #{{ $invoice->invoice_number }}</p>
                <div class="mt-2 flex items-baseline justify-center gap-1">
                    <span class="text-3xl font-bold text-gray-900">GHS {{ number_format($invoice->balance_due, 2) }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-2">Due Date: {{ $invoice->due_date->format('M d, Y') }}</p>
            </div>

            @php $fees = \App\Services\PaystackService::feeBreakdown((float) $invoice->balance_due); @endphp

            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-500">Customer</span>
                    <span class="font-medium text-gray-900">{{ $invoice->customer->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Service</span>
                    <span class="font-medium text-gray-900">{{ $invoice->customer->subscription->billing_cycle ?? 'Waste Collection' }}</span>
                </div>
                @if($fees['fee'] > 0)
                    <div class="flex justify-between mt-2 pt-2 border-t border-gray-200">
                        <span class="text-gray-500">Processing fee ({{ rtrim(rtrim(number_format($fees['percent'], 2), '0'), '.') }}%)</span>
                        <span class="font-medium text-gray-900">GHS {{ number_format($fees['fee'], 2) }}</span>
                    </div>
                    <div class="flex justify-between mt-2">
                        <span class="text-gray-700 font-semibold">Total to pay</span>
                        <span class="font-bold text-gray-900">GHS {{ number_format($fees['gross'], 2) }}</span>
                    </div>
                @endif
            </div>

            <form action="{{ route('public.pay.process', $invoice) }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-indigo-600 text-white rounded-lg py-3 font-semibold hover:bg-indigo-700 transition shadow-md flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Pay {{ $fees['fee'] > 0 ? 'GHS ' . number_format($fees['gross'], 2) : 'Now' }}
                </button>
            </form>
             
             <div class="mt-6 text-center">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/4b/Paystack_Logo.png" alt="Powered by Paystack" class="h-6 mx-auto opacity-50">
            </div>
        </div>
    </div>
</body>
</html>
