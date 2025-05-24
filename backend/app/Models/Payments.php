<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentsFactory> */
    use HasFactory;

    protected $fillable = ['passenger_id', 'ride_id', 'price', 'payment_method_id'];

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
