<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $files =    Storage::disk('public')->files('driver_licenses');
        $randomFile = fake()->randomElement($files);


        return [
            'user_id' => User::factory()->state(['role' => 'driver']),
            'driver_license' => $randomFile,
            'driver_license_number' => fake()->bothify('DL#######'),
            'is_verified' => fake()->boolean(90),
        ];
    }
}
