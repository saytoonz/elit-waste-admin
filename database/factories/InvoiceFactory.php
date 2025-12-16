<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 50, 500);
        $status = $this->faker->randomElement(['Pending', 'Paid', 'Overdue', 'Cancelled']);
        $balance = $status === 'Paid' ? 0 : ($status === 'Partial' ? $amount / 2 : $amount);

        return [
            'customer_id' => Customer::factory(),
            'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
            'amount' => $amount,
            'balance_due' => $balance,
            'due_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'status' => $status,
            'notes' => $this->faker->sentence,
        ];
    }
}
