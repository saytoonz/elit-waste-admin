<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('currency', 5);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->enum('status', ['Draft', 'Pending', 'Paid', 'Partial', 'Overdue', 'Cancelled'])->default('Pending');
            $table->enum('kind', ['Cycle', 'Prepay', 'Manual'])->default('Cycle');
            $table->unsignedInteger('cycles_covered')->default(1);
            $table->date('period_start');
            $table->date('period_end');
            $table->date('issued_at');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('paystack_reference')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('due_date');
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_invoices');
    }
};
