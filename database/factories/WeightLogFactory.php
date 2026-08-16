<?php

namespace Database\Factories;

use App\Models\WeightLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WeightLog>
 */
class WeightLogFactory extends Factory
{
    protected $model = WeightLog::class;

    public function definition(): array
    {
        $gross = $this->faker->randomFloat(2, 300, 1200);
        $tare  = $this->faker->randomFloat(2, 40, min(300, $gross));

        return [
            'logged_at'     => $this->faker->dateTimeBetween('-400 days', 'now'),
            'vehicle_label' => $this->faker->randomElement(['GR-234-21', 'GR-118-24', 'Bike-07', 'Bike-12', 'AS-990-19']),
            'trash_type'    => $this->faker->randomElement(WeightLog::TRASH_TYPES),
            'gross_weight'  => $gross,
            'tare_weight'   => $tare,
            'net_weight'    => max(0, $gross - $tare), // model saving hook also enforces this
            'notes'         => $this->faker->optional()->sentence(),
            'recorded_by'   => null,
        ];
    }
}
