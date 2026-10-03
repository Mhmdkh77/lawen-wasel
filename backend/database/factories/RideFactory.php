<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\RideGroup;
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
        $vehicle = Vehicle::inRandomOrder()->first() ?? Vehicle::factory()->create();
        $capacity = $vehicle->capacity;
        $bookedSeats = fake()->numberBetween(0, $capacity);

        return [
            'vehicle_id' => $vehicle->id,
            'ride_group_id' => RideGroup::factory(),
            'scheduled_time' => fake()->dateTimeBetween('-2 weeks', '+2 weeks'),
            'type' => fake()->randomElement(['to_institution', 'from_institution']),
            'booked_seats' => $bookedSeats,
            'available_seats' => $capacity - $bookedSeats,
            'status' => fake()->randomElement(['pending', 'active', 'completed', 'canceled']),
        ];
    }
}
