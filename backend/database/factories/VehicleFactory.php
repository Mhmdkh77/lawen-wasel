<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'driver_id' => Driver::factory(),
            'plate_number' => strtoupper($this->faker->bothify('??###??')),
            'brand' => fake()->company(),
            'color' => fake()->safeColorName(),
            'capacity' => fake()->numberBetween(4, 25),
        ];
    }
}
