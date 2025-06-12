<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationGroup extends Model
{

    protected $table = 'location_groups';
    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_group_location_rel')
            ->withPivot('location_type');
    }
}
