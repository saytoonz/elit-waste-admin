<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Step A of the duplicate-billing fix: add subscription linkage + billing period
 * to invoices. A NON-unique index for now — the unique constraint (Migration B)
 * is added only AFTER `billing:reconcile-duplicates --apply` has cleaned existing
 * duplicates, otherwise the unique index would fail to create.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('subscription_id')->nullable()->after('customer_id')
                ->constrained()->nullOnDelete();
            $table->string('billing_period')->nullable()->after('subscription_id');
            $table->index(['subscription_id', 'billing_period'], 'invoices_sub_period_idx');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_sub_period_idx');
            $table->dropForeign(['subscription_id']);
            $table->dropColumn(['subscription_id', 'billing_period']);
        });
    }
};
