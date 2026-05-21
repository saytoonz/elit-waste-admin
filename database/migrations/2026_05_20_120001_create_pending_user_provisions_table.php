<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_user_provisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->text('password'); // encrypted via cast
            $table->string('role')->default('Supervisor');
            $table->enum('status', ['Pending', 'Provisioned', 'Failed', 'Cancelled'])->default('Pending');
            $table->foreignId('provisioned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_user_provisions');
    }
};
