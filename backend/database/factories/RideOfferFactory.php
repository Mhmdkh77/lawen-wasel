<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RideOffer>
 */
class RideOfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Requires the caller to supply ride_request_id and driver_id explicitly.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'offered_price' => fake()->numberBetween(50, 200),
            'pickup_time' => fake()->dateTimeBetween('now', '+2 weeks'),
            'driver_message' => fake()->optional()->sentence(),
            'status' => 'pending',
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'accepted']);
    }

    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'rejected']);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => ['status' => 'expired']);
    }
}
