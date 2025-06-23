<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Faker\Provider\ar_EG\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function scopeDrivers($query)
    {
        return $query->where('role', 'driver');
    }

    public function scopePassengers($query)
    {
        return $query->where('role', 'passenger');
    }

    public function isPassenger(): bool
    {
        return $this->role === 'passenger';
    }

    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function passenger()
    {
        return $this->hasOne(Passenger::class);
    }

    public function vehicles()
    {
        return $this->driver ? $this->driver->vehicles() : $this->hasMany(Vehicle::class, 'driver_id')->whereRaw('0=1'); // empty relation
    }

    public function city()
    {
        return $this->belongsTo(Location::class, 'city_id');
    }

    public function ratingsGiven()
    {
        return $this->hasMany(Rating::class, 'rating_user_id');
    }

    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }
}
