<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_broadcasts', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('status');
            $table->string('failure_reason')->nullable()->after('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::table('sms_broadcasts', function (Blueprint $table) {
            $table->dropColumn(['scheduled_at', 'failure_reason']);
        });
    }
};
