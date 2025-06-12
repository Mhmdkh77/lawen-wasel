<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideTemplateGroup extends Model
{
    protected $table = 'ride_template_groups';

    protected $guarded = [];
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function locationGroup()
    {
        return $this->belongsTo(LocationGroup::class);
    }

    public function rideTemplates()
    {
        return $this->hasMany(RideTemplate::class);
    }
}
