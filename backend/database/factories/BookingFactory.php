<?php

namespace Database\Factories;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */


    public function definition(): array
    {
        $firstRide = Ride::withOpenSeats()->inRandomOrder()->first();

        $passengers = $firstRide->passengers()->pluck('id');

        $passenger = User::passengers()->whereNotIn('id', $passengers)->inRandomOrder()->first();

        $status = in_array($firstRide->status, ['pending'])
            ? ['pending', 'accepted', 'canceled', 'rejected']
            : ['accepted', 'canceled', 'rejected'];

        return [
            'first_ride_id' => $firstRide->id,
            'passenger_id' => $passenger->id,
            'status' => fake()->randomElement($status),
            'nb_seats' => 1,
            'booking_time' => fake()->dateTimeBetween('-1 week', now()),
            'type' => 'one_way',
            'price' => fake()->numberBetween(10, 100)
        ];
    }

    public function roundTrip(): static
    {
        $firstRide = Ride::withOpenSeats()->inRandomOrder()->first();
        $secondRide = Ride::withOpenSeats()->where('id', '!=', $firstRide->id)->inRandomOrder()->first();
        $passengers = $firstRide->passengers()->pluck('id');
        $passengers = $passengers->merge($secondRide->passengers()->pluck('id'));
        $passenger = User::passengers()->whereNotIn('id', $passengers)->inRandomOrder()->first();

        return $this->state(fn(array $attributes) => [
            'first_ride_id' => $firstRide->id,
            'second_ride_id' => $secondRide->id,
            'passenger_id' => $passenger->id,
            'type' => 'round_trip',
        ]);
    }
}
