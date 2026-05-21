<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_service_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->string('currency', 5);
            $table->enum('billing_cycle', ['Monthly', 'Quarterly', 'Yearly'])->default('Monthly');
            $table->enum('status', ['Active', 'Paused', 'Cancelled', 'Suspended'])->default('Active');
            $table->date('start_date');
            $table->date('next_billing_date');
            $table->date('last_billed_date')->nullable();
            $table->boolean('auto_renew')->default(true);

            // Enforcement
            $table->boolean('force_payment')->default(false);
            $table->unsignedInteger('grace_days')->default(7);
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();

            // Snapshot of catalog metadata at sub time
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('next_billing_date');
            $table->index('force_payment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_subscriptions');
    }
};
