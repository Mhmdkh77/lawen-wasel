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
        $ride = Ride::where('status', '=', 'completed')->inRandomOrder()->first(); // Get random ride
        $driver = $ride->driver_id; // Get ride driver
        $passengers = $ride->passengers()->pluck('users.id')->toArray(); // Get ride passengers

        // get passenger that did not rate before
        $passenger = User::whereIn('id', $passengers)->whereNotIn('id', function ($query) use ($driver, $ride) {
            $query->select('rating_user_id')
                ->from('ratings')
                ->where('rated_user_id', $driver)
                ->where('ride_id', $ride->id);
        })->inRandomOrder()->first();

        return [
            'rated_user_id' => $driver,
            'rating_user_id' => $passenger->id,
            'ride_id' => $ride->id,
            'rating' => fake()->numberBetween(0, 5),
            'review_text' => fake()->realText()
        ];
    }
}
