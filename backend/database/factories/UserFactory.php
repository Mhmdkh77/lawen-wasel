<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $city = Location::cities()->inRandomOrder()->first();

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('pass'),
            'remember_token' => Str::random(10),
            'phone' => fake()->phoneNumber(),
            'role' => 'passenger',
            'gender' => fake()->randomElement(['male', 'female']),
            'latitude' => $city->latitude + fake()->randomFloat(6, -0.0002, 0.0002),
            'longitude' => $city->longitude + fake()->randomFloat(6, -0.0002, 0.0002),
            'city_id' => $city->id
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function driver(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'driver',
            'driver_license' => strtoupper(Str::random(10)),
        ]);
    }
}
