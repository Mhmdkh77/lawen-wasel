<?php

namespace Database\Factories;

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
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('pass'),
            'remember_token' => Str::random(10),
            'phone' => fake()->phoneNumber(),
            'role' => 'passenger',
            'gender' => fake()->randomElement(['male', 'female']),
            'latitude' => $this->faker->randomFloat(6, 33.05, 34.7),
            'longitude' => $this->faker->randomFloat(6, 35.1, 36.6),
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
