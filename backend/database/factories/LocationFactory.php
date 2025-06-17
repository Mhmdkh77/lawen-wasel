<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $city = Location::cities()->inRandomOrder()->first();

        return [
            'name' => $city->name . 'Station',
            'type' => 'station',
            'latitude' => $city->latitude + fake()->randomFloat(6, -0.002, 0.002),
            'longitude' => $city->longitude + fake()->randomFloat(6, -0.002, 0.002),
            'city_id' => $city->id
        ];
    }
}
