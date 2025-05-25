<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\PaymentMethod;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payments>
 */
class PaymentsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $passenger = User::passengers()->inRandomOrder()->first();

        $booking = Booking::whereNotIn('id', function ($query) use ($passenger) {
            $query->select('booking_id')
                ->from('payments')
                ->where('passenger_id', $passenger->id);
        })->inRandomOrder()->first();

        if (!$booking || !$passenger) {
            return [
                'passenger_id' => null,
                'booking_id' => null,
                'total' => 0,
                'payment_method_id' => null,
            ];
        }

        return [
            'passenger_id' => $passenger->id,
            'booking_id' => $booking->id,
            'total' => $booking->price,
            'payment_method_id' => PaymentMethod::first()
        ];
    }
}
