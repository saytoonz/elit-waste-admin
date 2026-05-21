<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_subscription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity_total');
            $table->unsignedInteger('quantity_used')->default(0);
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('status', ['Active', 'Exhausted', 'Expired', 'Cancelled'])->default('Active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'period_end']);
            $table->index('platform_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_bundles');
    }
};
