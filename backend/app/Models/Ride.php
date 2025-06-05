<?php

namespace App\Models;

use Faker\Provider\ar_EG\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ride extends Model
{
    /** @use HasFactory<\Database\Factories\RideFactory> */
    use HasFactory;

    protected $guarded = [];

    // public function scopeWithOpenSeats($query)
    // {
    //     return $query->whereHas('vehicle')
    //         ->whereRaw("
    //         (select COALESCE(SUM(nb_seats), 0) 
    //          from bookings 
    //          where bookings.ride_id = rides.id 
    //            and bookings.status = 'accepted') < 
    //         (select capacity from vehicles where vehicles.id = rides.vehicle_id)
    //     ");
    // }

    public function scopeWithOpenSeats($query)
    {
        return $query->whereHas('vehicle', function ($q) {
            $q->whereColumn('rides.booked_seats', '<', 'vehicles.capacity');
        });
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
    public function passengers()
    {
        return $this->hasManyThrough(
            User::class,
            Booking::class,
            'ride_id',       // Foreign key on bookings table...
            'id',            // Foreign key on users table...
            'id',            // Local key on rides table...
            'passenger_id'   // Local key on bookings table...
        );
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

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'ride_location')->withPivot('type');
    }
}
