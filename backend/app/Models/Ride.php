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



    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function rideGroup()
    {
        return $this->belongsTo(RideGroup::class);
    }

    public function nodes()
    {
        return $this->hasMany(Node::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function rideRequests()
    {
        return $this->hasMany(RideRequest::class);
    }

    public function driver()
    {
        return $this->rideGroup->driver();
    }

    public function passengers()
    {
        return $this->hasManyThrough(
            Passenger::class,
            Booking::class,
            'ride_id',       // Foreign key on bookings table...
            'id',            // Foreign key on users table...
            'id',            // Local key on rides table...
            'passenger_id'   // Local key on bookings table...
        );
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function locations($type)
    {
        return $this->rideGroup->locationGroup->locations
            ->filter(fn($loc) => $loc->pivot->location_type === $type)
            ->values();
    }

    public function scopeWithOpenSeats($query)
    {
        return $query->where('available_seats', '>', 0);
    }
}
