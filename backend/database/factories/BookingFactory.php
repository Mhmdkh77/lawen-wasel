<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingGroup;
use App\Models\Node;
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

        $ride = Ride::withOpenSeats()->inRandomOrder()->first(); // Get random ride with open seats


        if (!$ride) {
            throw new \Exception('No rides with open seats available');
        }

        $ride->refresh();

        // Lock the row to prevent concurrent modifications
        $ride = Ride::where('id', $ride->id)->lockForUpdate()->first();

        $passenger = User::whereDoesntHave('bookings', function ($query) use ($ride) {
            $query->where('ride_id', $ride->id);
        })->inRandomOrder()->first();

        if (!$passenger) {
            throw new \Exception('No available passengers for this ride');
        }


        $ride->increment('booked_seats');


        $city = $ride->locations()->cities()->inRandomOrder()->first();

        $destination = $ride->locations()->institutions()->inRandomOrder()->value('locations.id');

        // Get status of the booking options
        $status = in_array($ride->status, ['pending'])
            ? ['pending', 'accepted']
            : ['accepted'];


        return [
            'ride_id' => $ride->id,
            'booking_group_id' => BookingGroup::create([
                'passenger_id' => $passenger->id
            ])->id,
            'node_id' => Node::factory()->create([
                'ride_id' => $ride->id,
                'type' => 'pickup',
                'latitude' => $city->latitude + fake()->randomFloat(6, -0.0002, 0.0002),
                'longitude' => $city->longitude + fake()->randomFloat(6, -0.0002, 0.0002),
                'destination_id' => $destination
            ]),
            'destination_id' => $destination,
            'passenger_id' => $passenger->id,
            'status' => fake()->randomElement($status),
            'nb_seats' => 1,
            'arrival_time' =>  $ride->arrival_time,
            'type' => 'one_way',
            'price' => fake()->numberBetween(10, 100)
        ];
    }

    public function canceledOrRejeceted()
    {
        return $this->state(fn(array $attributes) => [
            'node_id' => null,
            'status' => fake()->randomElement(['canceled', 'rejected'])
        ]);
    }
}
