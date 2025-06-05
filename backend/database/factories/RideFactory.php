<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ride>
 */
class RideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $driver = User::drivers()->inRandomOrder()->first(); // Get random driver
        $vehicle = Vehicle::where('driver_id', $driver->id)->inRandomOrder()->first(); // Get random vehicle for the choosen driver

        // Random date and time
        $baseDate = $this->faker->dateTimeBetween('now', '+2 weeks');
        $arrivalHour = $this->faker->randomElement([8, 9, 10]);
        $arrivalDateTime = \Carbon\Carbon::instance($baseDate)->setTime($arrivalHour, 0, 0);

        return [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => fake()->dateTime(),
            'finish_time' => fake()->dateTime(),
            'arrival_time' => $arrivalDateTime,
            'status' => fake()->randomElement(['pending', 'active',  'completed'])
        ];
    }
}
