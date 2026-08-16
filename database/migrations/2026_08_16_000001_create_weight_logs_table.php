<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weight_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('logged_at');
            $table->string('vehicle_label');
            $table->enum('trash_type', ['General', 'Organic', 'Recyclable', 'Hazardous'])->default('General');
            $table->decimal('gross_weight', 12, 2);
            $table->decimal('tare_weight', 12, 2);
            $table->decimal('net_weight', 12, 2);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('logged_at');
            $table->index('trash_type');
            $table->index(['trash_type', 'logged_at']);
            $table->index('vehicle_label');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weight_logs');
    }
};
