<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Faker\Provider\ar_EG\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'gender',
        'latitude',
        'longitude',
        'city_id'
    ];


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
        return $this->type === 'passenger';
    }

    public function isDriver(): bool
    {
        return $this->type === 'driver';
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
        return $this->driver ? $this->driver->vehicles() : collect();
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
