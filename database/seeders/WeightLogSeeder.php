<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WeightLog;
use Illuminate\Database\Seeder;

/**
 * Demo weighing records for testing the analytics dashboard.
 * Run explicitly:  php artisan db:seed --class=WeightLogSeeder
 */
class WeightLogSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->value('id');

        WeightLog::factory()
            ->count(80)
            ->create(['recorded_by' => $userId]);
    }
}
