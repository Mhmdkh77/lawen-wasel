<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Requires the caller to supply ride_id, passenger_id, ride_request_id,
     * node_id and booking_group_id explicitly.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nb_seats' => 1,
            'price' => fake()->numberBetween(10, 100),
            'status' => 'active',
        ];
    }

    public function canceled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => fake()->randomElement(['passenger_canceled', 'ride_canceled']),
        ]);
    }
}
