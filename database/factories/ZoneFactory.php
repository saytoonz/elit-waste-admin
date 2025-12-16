<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ZoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Zone ' . $this->faker->unique()->city,
            'description' => $this->faker->sentence,
            'is_active' => true,
        ];
    }
}
