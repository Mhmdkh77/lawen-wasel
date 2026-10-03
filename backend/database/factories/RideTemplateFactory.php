<?php

namespace Database\Factories;

use App\Models\RideTemplateGroup;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RideTemplate>
 */
class RideTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'sunday'];

        return [
            'vehicle_id' => Vehicle::factory(),
            'ride_template_group_id' => RideTemplateGroup::factory(),
            'scheduled_time' => fake()->time('H:i:s'),
            'recurring_days' => fake()->randomElements($allDays, fake()->numberBetween(2, 5)),
            'is_active' => fake()->boolean(80),
            'last_generated_at' => fake()->optional(0.6)->dateTimeBetween('-1 week', 'now'),
            'type' => fake()->randomElement(['to_institution', 'from_institution']),
        ];
    }
}
