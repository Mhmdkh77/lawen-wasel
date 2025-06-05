<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    public function scopeCities($query)
    {
        return $query->where('locations.type', 'city');
    }

    public function scopeStations($query)
    {
        return $query->where('locations.type', 'station');
    }

    public function scopeInstitutions($query)
    {
        return $query->where('locations.type', 'institution');
    }

    public function rides()
    {
        return $this->belongsToMany(Ride::class, 'ride_location')->withPivot('type');
    }
}
