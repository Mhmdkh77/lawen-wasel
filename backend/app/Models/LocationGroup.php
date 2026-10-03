<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationGroup extends Model
{
    use HasFactory;

    protected $table = 'location_groups';
    protected $guarded = [];

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'location_group_location_rel')
            ->withPivot('location_type');
    }

    public function locationGroupLocationRels()
    {
        return $this->hasMany(LocationGroupLocationRel::class);
    }
}
