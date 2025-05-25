<?php

namespace App\Models;

use Faker\Provider\ar_EG\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ride extends Model
{
    /** @use HasFactory<\Database\Factories\RideFactory> */
    use HasFactory;

    protected $guarded = [];

    public function scopeWithOpenSeats($query)
    {
        return $query->whereHas('vehicle')
            ->whereHas('bookings', function ($q) {
                $q->selectRaw('ride_id, SUM(nb_seats) as booked_seats')
                    ->groupBy('ride_id')
                    ->havingRaw('SUM(nb_seats) < (select capacity from vehicles where vehicles.id = rides.vehicle_id)');
            });
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function passengers()
    {
        $route = $this->route()->with('nodes.passengers')->first();

        if (!$route) {
            return collect();
        }

        return $route->nodes->flatMap(fn($node) => $node->passengers)->unique('id');
    }

    public function acceptedBookings()
    {
        return $this->hasMany(Booking::class)->where('status', 'accepted');
    }
    public function availableSeats(): int
    {
        $bookedSeats = $this->acceptedBookings()->sum('nb_seats');
        $capacity = $this->vehicle?->capacity ?? 0;

        return max($capacity - $bookedSeats, 0);
    }
}
