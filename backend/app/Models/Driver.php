<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Driver extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function rideGroups()
    {
        return $this->hasMany(RideGroup::class);
    }

    public function rideTemplateGroups()
    {
        return $this->hasMany(RideTemplateGroup::class);
    }

    public function rideOffers()
    {
        return $this->hasMany(RideOffer::class);
    }
}
