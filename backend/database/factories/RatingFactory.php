<?php

namespace Database\Factories;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */


    public function definition(): array
    {
        $ride = Ride::inRandomOrder()->first();
        $driver = $ride->driver_id;
        $passengers = $ride->passengers()->pluck('id');

        $passenger = User::whereIn('id', $passengers)->whereNotIn('id', function ($query) use ($driver) {
            $query->select('rating_user_id')
                ->from('ratings')
                ->where('rated_user_id', $driver);
        })->inRandomOrder()->first();

        return [
            'rated_user_id' => $driver,
            'rating_user_id' => $passenger,
            'ride_id' => $ride->id,
            'rating' => fake()->numberBetween(0, 5),
            'review_text' => fake()->realText()
        ];
    }
}
