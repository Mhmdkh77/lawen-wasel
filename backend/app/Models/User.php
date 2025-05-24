<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Faker\Provider\ar_EG\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_name',
        'email',
        'password',
        'phone',
        'type',
        'gender',
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
            'password' => 'hashed',
        ];
    }

    public function isPassenger(): bool
    {
        return $this->type === 'passenger';
    }

    public function isDriver(): bool
    {
        return $this->type === 'driver';
    }

    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'driver_id');
    }

    public function rides()
    {
        return $this->hasMany(Ride::class, 'driver_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'passenger_id');
    }

    public function ratingsGiven()
    {
        return $this->hasMany(Rating::class, 'rating_user_id');
    }

    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'passenger_id');
    }
}
