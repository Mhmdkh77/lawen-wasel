<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleImage>
 */
class VehicleImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $files = Storage::disk('public')->files('vehicle_images');

        return [
            'vehicle_id' => Vehicle::factory(),
            'path' => fake()->randomElement($files),
        ];
    }
}
