<?php

namespace App\Policies;

use App\Models\RideTemplateGroup;
use App\Models\User;

class RideTemplateGroupPolicy
{
    /**
     * Create a new policy instance.
     */
    public function rud(User $user, RideTemplateGroup $rideTemplateGroup): bool
    {
        if (!$rideTemplateGroup->relationLoaded('driver')) {
            $rideTemplateGroup->load('driver');
        }

        return $rideTemplateGroup->driver->user_id === $user->id;
    }
}
