<?php

namespace Database\Factories;

use App\Models\Route;
use App\Models\User;
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

        return [
            'driver_id' => $driver->id,
            'route' => Route::factory(),
            'vehicle_id' => $driver->vehicles->inRandomOrder()->value('id'),
            'status' => fake()->randomElement(['pending', 'active',  'completed'])
        ];
    }
}
