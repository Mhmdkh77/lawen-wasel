<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideTemplate extends Model
{

    protected $table = 'ride_templates';
    protected $guarded = [];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function rideTemplateGroup()
    {
        return $this->belongsTo(RideTemplateGroup::class);
    }
}
