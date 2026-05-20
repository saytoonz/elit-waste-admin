<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained()->cascadeOnDelete();
            $table->enum('period', ['Monthly', 'Quarterly', 'Yearly'])->default('Monthly');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedTinyInteger('quarter')->nullable();
            $table->decimal('amount', 12, 2);
            $table->boolean('alert_enabled')->default(true);
            $table->unsignedTinyInteger('alert_threshold_percent')->default(80);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['expense_category_id', 'period', 'year', 'month', 'quarter'], 'budget_unique_period');
            $table->index(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_budgets');
    }
};
