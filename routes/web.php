<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'platform.block'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customer-facing portal (My Services / My Billing)
    Route::prefix('my')->name('my.')->group(function () {
        Route::get('/services', [\App\Http\Controllers\MyServicesController::class, 'index'])->name('services.index');
        Route::get('/services/email/add', [\App\Http\Controllers\MyServicesController::class, 'emailForm'])->name('services.email.form');
        Route::post('/services/email/add', [\App\Http\Controllers\MyServicesController::class, 'emailPurchase'])->name('services.email.purchase');
        Route::post('/services/{subscription}/quantity', [\App\Http\Controllers\MyServicesController::class, 'adjustQuantity'])->name('services.quantity');
        Route::post('/services/subscribe', [\App\Http\Controllers\MyServicesController::class, 'subscribe'])->name('services.subscribe');

        Route::get('/sms', [\App\Http\Controllers\MySmsBundleController::class, 'index'])->name('sms.index');

        Route::get('/invoices', [\App\Http\Controllers\MyBillingController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/callback', [\App\Http\Controllers\MyBillingController::class, 'callback'])->name('invoices.callback');
        Route::post('/invoices/pay-all', [\App\Http\Controllers\MyBillingController::class, 'payAll'])->name('invoices.payAll');
        Route::get('/invoices/{invoice}', [\App\Http\Controllers\MyBillingController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/pay', [\App\Http\Controllers\MyBillingController::class, 'pay'])->name('invoices.pay');
        Route::post('/subscriptions/{subscription}/prepay', [\App\Http\Controllers\MyBillingController::class, 'prepay'])->name('subscriptions.prepay');
    });

    // Provider-side platform management (SuperAdmin only)
    Route::prefix('platform')->name('platform.')->middleware('role:SuperAdmin')->group(function () {
        Route::resource('services', \App\Http\Controllers\Platform\PlatformServiceController::class)->except(['show']);
        Route::resource('subscriptions', \App\Http\Controllers\Platform\PlatformSubscriptionController::class)->except(['show']);
        Route::post('subscriptions/{subscription}/toggle-force', [\App\Http\Controllers\Platform\PlatformSubscriptionController::class, 'toggleForce'])->name('subscriptions.toggleForce');
        Route::post('subscriptions/{subscription}/reactivate', [\App\Http\Controllers\Platform\PlatformSubscriptionController::class, 'reactivate'])->name('subscriptions.reactivate');
        Route::post('subscriptions/{subscription}/bill-now', [\App\Http\Controllers\Platform\PlatformSubscriptionController::class, 'billNow'])->name('subscriptions.billNow');

        Route::get('invoices', [\App\Http\Controllers\Platform\PlatformInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [\App\Http\Controllers\Platform\PlatformInvoiceController::class, 'show'])->name('invoices.show');
        Route::post('invoices/{invoice}/mark-paid', [\App\Http\Controllers\Platform\PlatformInvoiceController::class, 'markPaid'])->name('invoices.markPaid');
        Route::post('invoices/{invoice}/cancel', [\App\Http\Controllers\Platform\PlatformInvoiceController::class, 'cancel'])->name('invoices.cancel');
        Route::delete('invoices/{invoice}', [\App\Http\Controllers\Platform\PlatformInvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::post('invoices/{invoice}/restore', [\App\Http\Controllers\Platform\PlatformInvoiceController::class, 'restore'])->name('invoices.restore');
        Route::delete('invoices/{invoice}/force', [\App\Http\Controllers\Platform\PlatformInvoiceController::class, 'forceDelete'])->name('invoices.forceDelete');

        // Provider settings (payment toggle, maintenance message)
        Route::get('settings', [\App\Http\Controllers\Platform\PlatformSettingsController::class, 'show'])->name('settings.show');
        Route::post('settings', [\App\Http\Controllers\Platform\PlatformSettingsController::class, 'update'])->name('settings.update');
    });
    
    Route::resource('users', \App\Http\Controllers\UserController::class)->middleware('permission:manage users');
    Route::resource('service_plans', \App\Http\Controllers\ServicePlanController::class);
    Route::resource('zones', \App\Http\Controllers\ZoneController::class);
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::resource('subscriptions', \App\Http\Controllers\SubscriptionController::class)->only(['store', 'update', 'destroy']);
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    
    // Payments
    Route::get('payments/initiate/{invoice}', [\App\Http\Controllers\PaymentController::class, 'initiate'])->name('payments.initiate');
    Route::get('payments/callback', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('payments.callback');
    Route::post('payments/cash/{invoice}', [\App\Http\Controllers\PaymentController::class, 'recordCash'])->name('payments.cash');
    Route::get('/payments/{payment}/print', [\App\Http\Controllers\PaymentController::class, 'print'])->name('payments.print');

    // SMS Broadcasts (message customers by zone, type, debtors, or selection)
    Route::middleware('role:Owner|Admin')->prefix('sms-broadcasts')->name('sms_broadcasts.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SmsBroadcastController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\SmsBroadcastController::class, 'create'])->name('create');
        Route::post('/estimate', [\App\Http\Controllers\SmsBroadcastController::class, 'estimate'])->name('estimate');
        Route::post('/', [\App\Http\Controllers\SmsBroadcastController::class, 'store'])->name('store');
        Route::get('/{sms_broadcast}', [\App\Http\Controllers\SmsBroadcastController::class, 'show'])->name('show');
        Route::get('/{sms_broadcast}/edit', [\App\Http\Controllers\SmsBroadcastController::class, 'edit'])->name('edit');
        Route::put('/{sms_broadcast}', [\App\Http\Controllers\SmsBroadcastController::class, 'update'])->name('update');
        Route::delete('/{sms_broadcast}', [\App\Http\Controllers\SmsBroadcastController::class, 'destroy'])->name('destroy');
        Route::post('/{sms_broadcast}/send-now', [\App\Http\Controllers\SmsBroadcastController::class, 'sendNow'])->name('sendNow');
        Route::post('/{sms_broadcast}/retry-failed', [\App\Http\Controllers\SmsBroadcastController::class, 'retryFailed'])->name('retryFailed');
        Route::post('/{sms_broadcast}/recipients/{recipient}/retry', [\App\Http\Controllers\SmsBroadcastController::class, 'retryRecipient'])->name('retryRecipient');
    });

    // Settings
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index')->middleware('role:Owner|Admin');
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update')->middleware('role:Owner|Admin');

    // Data Export
    Route::get('/export/{table}/csv', [\App\Http\Controllers\ExportController::class, 'csv'])->name('export.csv')->middleware('role:Owner|Admin|Accountant');
    Route::get('/export/{table}/pdf', [\App\Http\Controllers\ExportController::class, 'pdf'])->name('export.pdf')->middleware('role:Owner|Admin|Accountant');

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('role:Owner|Admin|Accountant')->group(function() {
        Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
        Route::get('/receivables', [\App\Http\Controllers\ReportController::class, 'receivables'])->name('receivables');
        Route::get('/revenue', [\App\Http\Controllers\ReportController::class, 'revenue'])->name('revenue');
        Route::get('/payments', [\App\Http\Controllers\ReportController::class, 'payments'])->name('payments');
        Route::get('/audit', [\App\Http\Controllers\ReportController::class, 'audit'])->name('audit');

        // Expense reports
        Route::get('/expenses', [\App\Http\Controllers\ReportController::class, 'expenses'])->name('expenses');
        Route::get('/profit-loss', [\App\Http\Controllers\ReportController::class, 'profitLoss'])->name('profit_loss');
        Route::get('/budget-variance', [\App\Http\Controllers\ReportController::class, 'budgetVariance'])->name('budget_variance');

        // Cash Approvals
        Route::get('/cash-approvals', [\App\Http\Controllers\ReportController::class, 'pendingCash'])->name('cash.pending');
        Route::post('/cash-approvals/{payment}', [\App\Http\Controllers\ReportController::class, 'approveCash'])->name('cash.approve');
    });

    // Expenses
    Route::resource('expense_categories', \App\Http\Controllers\ExpenseCategoryController::class)->except(['show'])
        ->middleware('role:Owner|Admin|Accountant');
    Route::resource('vendors', \App\Http\Controllers\VendorController::class)
        ->middleware('role:Owner|Admin|Accountant|Supervisor');
    Route::resource('expense_budgets', \App\Http\Controllers\ExpenseBudgetController::class)->except(['show'])
        ->middleware('role:Owner|Admin|Accountant');
    Route::resource('recurring_expenses', \App\Http\Controllers\RecurringExpenseController::class)->except(['show'])
        ->middleware('role:Owner|Admin|Accountant');
    Route::post('recurring_expenses/{recurring_expense}/run', [\App\Http\Controllers\RecurringExpenseController::class, 'runNow'])
        ->name('recurring_expenses.run')
        ->middleware('role:Owner|Admin|Accountant');

    Route::middleware('role:Owner|Admin|Accountant|Supervisor')->group(function () {
        Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);
        Route::get('expenses/{expense}/attachment', [\App\Http\Controllers\ExpenseController::class, 'downloadAttachment'])
            ->name('expenses.attachment');
    });

    Route::middleware('role:Owner|Admin|Accountant')->group(function () {
        Route::post('expenses/{expense}/approve', [\App\Http\Controllers\ExpenseController::class, 'approve'])->name('expenses.approve');
        Route::post('expenses/{expense}/reject', [\App\Http\Controllers\ExpenseController::class, 'reject'])->name('expenses.reject');
        Route::post('expenses/{expense}/pay', [\App\Http\Controllers\ExpenseController::class, 'markPaid'])->name('expenses.pay');
        Route::post('expenses/{expense}/cancel', [\App\Http\Controllers\ExpenseController::class, 'cancel'])->name('expenses.cancel');
    });
});

// Webhooks (Outside Auth Middleware)
Route::post('/webhook/paystack', [\App\Http\Controllers\WebhookController::class, 'handlePaystack'])->name('webhook.paystack');

// Public Payment Links
Route::get('/pay/callback', [\App\Http\Controllers\PublicPaymentController::class, 'callback'])->name('public.pay.callback');
Route::get('/pay/{invoice}', [\App\Http\Controllers\PublicPaymentController::class, 'show'])->name('public.pay.show');
Route::post('/pay/{invoice}', [\App\Http\Controllers\PublicPaymentController::class, 'process'])->name('public.pay.process');

require __DIR__.'/auth.php';
