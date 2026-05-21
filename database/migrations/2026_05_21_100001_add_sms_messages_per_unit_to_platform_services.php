<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_services', function (Blueprint $table) {
            // For SMS-type services: how many actual SMS messages 1 unit of the subscription grants.
            // E.g. a $10 "SMS Bundle" unit grants 1000 messages. Nullable for non-SMS services.
            $table->unsignedInteger('sms_messages_per_unit')->nullable()->after('min_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('platform_services', function (Blueprint $table) {
            $table->dropColumn('sms_messages_per_unit');
        });
    }
};
