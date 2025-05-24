<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    /** @use HasFactory<\Database\Factories\NodeFactory> */
    use HasFactory;

    protected $fillable = ['latitude', 'longitude', 'arrival_time', 'station_id', 'route_id'];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function passengers()
    {
        return $this->belongsToMany(User::class, 'node_passenger', 'node_id', 'passenger_id');
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'node_passenger');
    }
}
