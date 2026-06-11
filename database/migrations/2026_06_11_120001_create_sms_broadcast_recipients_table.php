<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_broadcast_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_broadcast_id')->constrained('sms_broadcasts')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('phone');
            $table->text('message');
            $table->unsignedInteger('credits')->default(0); // credits actually consumed (0 until Sent)
            $table->string('status')->default('Queued');    // Queued | Sent | Failed | Skipped
            $table->string('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['sms_broadcast_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_broadcast_recipients');
    }
};
