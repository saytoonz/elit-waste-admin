<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->string('gateway_message_id')->nullable()->after('gateway_status'); // MyCSMS data.results[].message_id
            $table->string('gateway_state', 40)->nullable()->after('gateway_message_id'); // MyCSMS per-recipient status, e.g. "processed"
            $table->decimal('gateway_cost', 10, 4)->nullable()->after('gateway_state');
            $table->index('gateway_message_id');
        });
    }

    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropIndex(['gateway_message_id']);
            $table->dropColumn(['gateway_message_id', 'gateway_state', 'gateway_cost']);
        });
    }
};
