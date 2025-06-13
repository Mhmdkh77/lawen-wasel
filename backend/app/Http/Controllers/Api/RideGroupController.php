<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RideGroup;
use App\Models\RideRequest;
use Illuminate\Http\Request;

class RideGroupController extends Controller
{
    public function show(Request $request, RideGroup $rideGroup)
    {
        $passenger = $request->user()?->passenger;

        $requestedRideIds = [];

        if ($passenger) {
            $requestedRideIds = RideRequest::where('passenger_id', $passenger->id)
                ->where(function ($q) use ($rideGroup) {
                    $q->whereIn('to_inst_ride_id', $rideGroup->rides->pluck('id'))
                        ->orWhereIn('from_inst_ride_id', $rideGroup->rides->pluck('id'));
                })
                ->get()
                ->flatMap(function ($request) {
                    return array_filter([
                        $request->to_inst_ride_id,
                        $request->from_inst_ride_id,
                    ]);
                })
                ->unique()
                ->toArray();
        }

        $rideGroup->load([
            'driver.user',
            'rides.vehicle',
        ]);

        $toInstitutionRides = $rideGroup->rides
            ->where('type', 'to_institution')
            ->values();

        $fromInstitutionRides = $rideGroup->rides
            ->where('type', 'from_institution')
            ->values();

        return response()->json([
            'ride_group' => [
                'id' => $rideGroup->id,
                'driver' => [
                    'id' => $rideGroup->driver->id,
                    'name' => $rideGroup->driver->user->name,
                    'email' => $rideGroup->driver->user->email,
                ],
            ],
            'to_institution_rides' => $toInstitutionRides,
            'from_institution_rides' => $fromInstitutionRides,
            'requestedRideIds' => $requestedRideIds,
        ]);
    }
}
