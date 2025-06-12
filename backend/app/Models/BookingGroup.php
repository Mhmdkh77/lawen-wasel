<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingGroup extends Model
{
    use HasFactory;

    protected $table = 'bookings_group';

    protected $guarded = [];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function passenger()
    {
        return $this->belongsTo(Passenger::class);
    }
}
