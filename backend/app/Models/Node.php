<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    /** @use HasFactory<\Database\Factories\NodeFactory> */
    use HasFactory;

    protected $guarded = [];

    public function scopePickup($query)
    {
        return $query->where('type', 'pickup');
    }

    public function scopeDropoff($query)
    {
        return $query->where('type', 'dropoff');
    }

    public function destination()
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }
}
