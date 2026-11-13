<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per SMS the app tries to send through the gateway (MyCSMS), whatever
 * triggered it, so admins can see every message and its outcome in one place.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->text('message');
            $table->string('type', 40)->default('other');     // payment | invoice | reminder | broadcast | welcome | admin_reminder | lead_alert | other
            $table->string('profile', 20)->default('customer');
            $table->string('status', 20)->default('Queued');  // Queued | Sent | Failed | Skipped
            $table->unsignedInteger('credits')->default(0);   // credits actually consumed
            $table->unsignedInteger('attempts')->default(0);
            $table->string('error')->nullable();
            $table->unsignedSmallInteger('gateway_status')->nullable(); // HTTP status from the gateway
            $table->text('gateway_response')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('sms_broadcast_id')->nullable()->constrained('sms_broadcasts')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
