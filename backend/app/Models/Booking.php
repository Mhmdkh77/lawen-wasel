<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $guarded = [];

    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function bookingGroup()
    {
        return $this->belongsTo(BookingGroup::class);
    }

    public function rideRequest()
    {
        return $this->belongsTo(RideRequest::class);
    }

    public function node()
    {
        return $this->belongsTo(Node::class);
    }
}
