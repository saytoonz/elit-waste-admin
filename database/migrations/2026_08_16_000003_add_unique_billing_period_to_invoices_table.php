<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Step B of the duplicate-billing fix: enforce one invoice per
 * (subscription, billing_period) at the database level.
 *
 * RUN ORDER: migrate (A) -> billing:reconcile-duplicates --apply -> migrate (B).
 * Running this before cleanup will fail because duplicate rows still exist.
 *
 * NULLs are treated as distinct by MySQL, so manual invoices (no subscription_id
 * / billing_period) and cancelled duplicate rows (billing_period nulled by the
 * reconcile command) never collide.
 *
 * The unique index is added BEFORE dropping the plain index so the
 * subscription_id foreign key always has a covering index during the swap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unique(['subscription_id', 'billing_period'], 'invoices_sub_period_unique');
            $table->dropIndex('invoices_sub_period_idx');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['subscription_id', 'billing_period'], 'invoices_sub_period_idx');
            $table->dropUnique('invoices_sub_period_unique');
        });
    }
};
