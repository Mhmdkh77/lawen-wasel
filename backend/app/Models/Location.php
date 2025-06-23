<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $guarded = [];

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

    public function city()
    {
        return $this->belongsTo(Location::class, 'city_id');
    }

    public function children()
    {
        return $this->hasMany(Location::class, 'city_id');
    }

    public function passengers()
    {
        return $this->hasMany(User::class, 'city_id');
    }
}
