<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\Response;

class VehiclePolicy
{

    /**
     * Determine whether the user can view the model.
     */
    public function rud(User $user, Vehicle $vehicle): bool
    {
        if (!$vehicle->relationLoaded('driver')) {
            $vehicle->load('driver');
        }

        return $vehicle->driver->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'driver';
    }
}
