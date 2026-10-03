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
        $ride = Ride::where('status', '=', 'completed')->whereHas('bookings')->inRandomOrder()->first();
        $driverUserId = $ride->driver->user_id;
        $passengerUserIds = $ride->passengers()->pluck('passengers.user_id')->toArray();

        $passenger = User::whereIn('id', $passengerUserIds)->whereNotIn('id', function ($query) use ($driverUserId, $ride) {
            $query->select('rating_user_id')
                ->from('ratings')
                ->where('rated_user_id', $driverUserId)
                ->where('ride_id', $ride->id);
        })->inRandomOrder()->first();

        return [
            'rated_user_id' => $driverUserId,
            'rating_user_id' => $passenger->id,
            'ride_id' => $ride->id,
            'rating' => fake()->numberBetween(1, 5),
            'review_text' => fake()->realText(),
        ];
    }
}
