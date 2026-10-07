<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Passenger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RideRequest>
 */
class RideRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $institution = Location::institutions()->inRandomOrder()->first();
        $pickupCity = Location::cities()->inRandomOrder()->first();

        return [
            'passenger_id' => Passenger::factory(),
            'passenger_latitude' => $pickupCity->latitude + $this->faker->randomFloat(6, -0.002, 0.002),
            'passenger_longitude' => $pickupCity->longitude + $this->faker->randomFloat(6, -0.002, 0.002),
            'institution_location_id' => $institution->id,
            'nb_seats_requested' => 1,
            'notes' => fake()->optional()->sentence(),
            'type' => 'one_way',
            'status' => 'pending',
        ];
    }

    public function driverOffered(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'driver_offered']);
    }

    public function accepted(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'accepted']);
    }

    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'rejected']);
    }

    public function canceled(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'canceled']);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'expired']);
    }
}
