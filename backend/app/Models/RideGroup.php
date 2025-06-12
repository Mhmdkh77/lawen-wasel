<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideGroup extends Model
{
    protected $table = 'ride_groups';
    protected $guarded = [];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function locationGroup()
    {
        return $this->belongsTo(LocationGroup::class);
    }

    public function rides()
    {
        return $this->hasMany(Ride::class);
    }
}
