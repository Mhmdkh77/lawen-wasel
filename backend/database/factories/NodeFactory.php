<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Ride;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Node>
 */
class NodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dropoff = Location::institutions()->inRandomOrder()->first();

        return [
            'ride_id' => Ride::factory(),
            'pickup_location_id' => null,
            'pickup_latitude' => $this->faker->randomFloat(6, 33.05, 34.7),
            'pickup_longitude' => $this->faker->randomFloat(6, 35.1, 36.6),
            'dropoff_location_id' => $dropoff->id,
            'dropoff_latitude' => $dropoff->latitude,
            'dropoff_longitude' => $dropoff->longitude,
            'status' => 'pending',
        ];
    }
}
