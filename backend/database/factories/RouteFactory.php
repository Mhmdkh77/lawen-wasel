<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Route>
 */
class RouteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = $this->faker->dateTimeBetween('+0 days', '+1 week');
        $duration = $this->faker->numberBetween(10, 120);

        return [
            'start_time' => $startTime,
            'finish_time' => (clone $startTime)->modify("+$duration minutes"),
            'start_latitude' => $this->faker->latitude,
            'start_longitude' => $this->faker->longitude,
            'destination_latitude' => $this->faker->latitude,
            'destination_longitude' => $this->faker->longitude,
            'distance_km' => $this->faker->randomFloat(2, 1, 100), // up to 2 decimals
            'duration_minutes' => $duration,
        ];
    }
}
