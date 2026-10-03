<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\LocationGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RideTemplateGroup>
 */
class RideTemplateGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Morning Route', 'Evening Route', 'Weekday Commute', 'Campus Shuttle']),
            'driver_id' => Driver::factory(),
            'location_group_id' => LocationGroup::factory(),
            'is_active' => fake()->boolean(80),
        ];
    }
}
