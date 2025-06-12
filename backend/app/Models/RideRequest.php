<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideRequest extends Model
{
    protected $guarded = [];

    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function passengerLocation()
    {
        return $this->belongsTo(Location::class, 'passenger_location_id');
    }

    public function institutionLocation()
    {
        return $this->belongsTo(Location::class, 'institution_location_id');
    }

    public function rideOffers()
    {
        return $this->hasOne(RideOffer::class);
    }
}
