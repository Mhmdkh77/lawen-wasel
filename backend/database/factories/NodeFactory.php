<?php

namespace Database\Factories;

use App\Models\Station;
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

        return [
            'latitude' => $this->faker->randomFloat(6, 33.05, 34.7),
            'longitude' => $this->faker->randomFloat(6, 35.1, 36.6),
        ];
    }

    // public function withStation(): static
    // {
    //     $station = Station::inRandomOrder()->value('id');

    //     return $this->state(fn(array $attributes) => [
    //         'latitude' => $station->latitude(),
    //         'longitude' => $station->longitude(),
    //         'station_id' => $station
    //     ]);
    // }
}
