<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ExpenseCategorySeeder::class,
        ]);

        // seed data
        $zones = \App\Models\Zone::factory()->count(5)->create();

        \App\Models\Customer::factory()
            ->count(50)
            ->recycle($zones) // Use created zones
            ->create()
            ->each(function ($customer) {
                // Attach subscription
                \App\Models\Subscription::factory()->create([
                    'customer_id' => $customer->id,
                ]);

                // Create some invoices
                \App\Models\Invoice::factory()->count(rand(1, 5))->create([
                    'customer_id' => $customer->id,
                ]);
            });
    }
}
