<?php

namespace Database\Factories;

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
            'driver_id' => User::drivers()->inRandomOrder()->value('id'),
            'plate_number' => strtoupper($this->faker->bothify('??###??')),
            'type' => $this->faker->randomElement(['Sedan', 'SUV', 'Truck', 'Van', 'Coupe']),
            'model' => $this->faker->word(),
            'color' => $this->faker->safeColorName(),
            'capacity' => $this->faker->numberBetween(1, 8),
        ];
    }
}
