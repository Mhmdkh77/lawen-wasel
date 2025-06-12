<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideOffer extends Model
{
    protected $guarded = [];

    public function rideRequest()
    {
        return $this->belongsTo(RideRequest::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function suggestedPickupLocation()
    {
        return $this->belongsTo(Location::class, 'suggested_pickup_location_id');
    }
}
