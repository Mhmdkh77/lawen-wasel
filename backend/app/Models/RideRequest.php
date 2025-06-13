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

    public function toInstRide()
    {
        return $this->belongsTo(Ride::class, 'to_inst_ride_id');
    }

    public function fromInstRide()
    {
        return $this->belongsTo(Ride::class, 'from_inst_ride_id');
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
