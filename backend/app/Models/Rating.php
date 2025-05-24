<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    /** @use HasFactory<\Database\Factories\RatingFactory> */
    use HasFactory;

    protected $fillable = ['rated_user_id', 'rating_user_id', 'ride_id', 'rating', 'review_text'];

    public function ratedUser()
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }

    public function ratingUser()
    {
        return $this->belongsTo(User::class, 'rating_user_id');
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }
}
