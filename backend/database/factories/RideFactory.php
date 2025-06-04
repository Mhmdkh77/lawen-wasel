<?php

namespace Database\Factories;

use App\Models\Route;
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
        $driver = User::drivers()->inRandomOrder()->first();
        $vehicle = Vehicle::where('driver_id', $driver->id)->inRandomOrder()->first();

        if (!$vehicle) {
            $vehicle = Vehicle::factory()->create(['driver_id' => $driver->id]);
        }

        return [
            'driver_id' => $driver->id,
            'route_id' => Route::factory(),
            'vehicle_id' => $vehicle->id,
            'status' => fake()->randomElement(['pending', 'active',  'completed'])
        ];
    }
}
