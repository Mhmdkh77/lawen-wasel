<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    /** @use HasFactory<\Database\Factories\RouteFactory> */
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
        'start_latitude',
        'start_longitude',
        'destination_latitude',
        'destination_longitude',
        'distance_km',
        'duration_minutes',
        'price'
    ];

    public function rides()
    {
        return $this->hasMany(Ride::class, 'first_route');
    }

    public function nodes()
    {
        return $this->hasMany(Node::class);
    }
}
