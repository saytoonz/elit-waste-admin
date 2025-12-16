<?php

namespace Database\Factories;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'phone' => $this->faker->unique()->phoneNumber,
            'secondary_phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'landmark' => $this->faker->streetName,
            'zone_id' => Zone::factory(),
            'gps_coordinates' => $this->faker->latitude . ',' . $this->faker->longitude,
            'type' => $this->faker->randomElement(['Residential', 'Commercial']),
            'notes' => $this->faker->sentence,
            'is_active' => $this->faker->boolean(90), // 90% active
        ];
    }
}
