<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    /** @use HasFactory<\Database\Factories\RouteFactory> */
    use HasFactory;

    protected $guarded = [];

    public function ride()
    {
        return $this->hasOne(Ride::class);
    }

    public function nodes()
    {
        return $this->hasMany(Node::class);
    }
}
