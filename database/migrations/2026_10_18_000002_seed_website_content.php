<?php

use Database\Seeders\WebsiteContentSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Installs the starter website (pages, sections, menu, settings, media library,
 * sample news) so a plain `php artisan migrate` on the cPanel server gives a
 * working site. The seeder is idempotent and skips anything already present.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new WebsiteContentSeeder())->run();
    }

    public function down(): void
    {
        // Content is dropped with the tables in the previous migration's down().
    }
};
