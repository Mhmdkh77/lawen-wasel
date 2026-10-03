<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideTemplate extends Model
{
    use HasFactory;

    protected $table = 'ride_templates';
    protected $guarded = [];

    protected $casts = [
        'recurring_days' => 'array',
        'last_generated_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function rideTemplateGroup()
    {
        return $this->belongsTo(RideTemplateGroup::class);
    }
}
