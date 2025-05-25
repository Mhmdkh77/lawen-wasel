<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $guarded = [];
    public function firstRide()
    {
        return $this->belongsTo(Ride::class, 'first_ride_id');
    }

    public function secondRide()
    {
        return $this->belongsTo(Ride::class, 'second_ride_id');
    }
    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }
}
