<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        $billingCycle = $this->faker->randomElement(['Weekly', 'Monthly']);
        $amount = $billingCycle === 'Weekly' ? 50 : 200;
        
        return [
            'customer_id' => Customer::factory(),
            'amount' => $amount,
            'billing_cycle' => $billingCycle,
            'start_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'due_date_offset_days' => 7,
            'next_billing_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => 'Active',
        ];
    }
}
