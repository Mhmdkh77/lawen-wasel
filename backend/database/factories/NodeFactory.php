<?php

namespace Database\Factories;

use App\Models\Route;
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
        $route =  Route::inRandomOrder()->first();

        return [
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'route_id' => $route->id,
            'scheduled_time' => fake()->dateTimeBetween($route->start_time, $route->finish_time),   
        ];
    }

    public function withStation(): static
    {
        $station = Station::inRandomOrder()->value('id');

        return $this->state(fn(array $attributes) => [
            'latitude' => $station->latitude(),
            'longitude' => $station->longitude(),
            'station_id' => $station
        ]);
    }
}
