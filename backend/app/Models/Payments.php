<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentsFactory> */
    use HasFactory;

    protected $guarded = [];

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
