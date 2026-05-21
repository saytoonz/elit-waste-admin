<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['Email', 'Hosting', 'Domain', 'SMS', 'Storage', 'Other'])->default('Other');
            $table->decimal('unit_price', 12, 2);
            $table->string('currency', 5)->default('USD');
            $table->enum('billing_cycle', ['Monthly', 'Quarterly', 'Yearly'])->default('Monthly');
            $table->boolean('is_quantity_based')->default(false);
            $table->string('unit_label')->nullable();
            $table->unsignedInteger('default_quantity')->default(1);
            $table->unsignedInteger('min_quantity')->default(1);
            $table->unsignedInteger('grace_days')->default(7);
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->boolean('customer_addable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_services');
    }
};
