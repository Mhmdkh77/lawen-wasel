<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\LocationGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RideGroup>
 */
class RideGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'driver_id' => Driver::inRandomOrder()->first()?->id ?? Driver::factory(),
            'location_group_id' => LocationGroup::factory(),
        ];
    }
}
