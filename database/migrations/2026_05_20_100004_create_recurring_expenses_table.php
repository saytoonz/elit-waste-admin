<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->enum('frequency', ['Daily', 'Weekly', 'Monthly', 'Quarterly', 'Yearly']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_run_date');
            $table->date('last_run_date')->nullable();
            $table->enum('payment_method', ['Cash', 'Bank Transfer', 'Mobile Money', 'Card', 'Cheque', 'Other'])->default('Bank Transfer');
            $table->boolean('auto_approve')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('is_active');
            $table->index('next_run_date');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreign('recurring_expense_id')->references('id')->on('recurring_expenses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['recurring_expense_id']);
        });
        Schema::dropIfExists('recurring_expenses');
    }
};
