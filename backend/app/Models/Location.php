<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    public function scopeCities($query)
    {
        return $query->where('type', 'city');
    }

    public function scopeStations($query)
    {
        return $query->where('type', 'station');
    }

    public function scopeInstitutions($query)
    {
        return $query->where('type', 'institution');
    }   
}
