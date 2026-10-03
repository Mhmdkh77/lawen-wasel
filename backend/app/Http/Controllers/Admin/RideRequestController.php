<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RideRequest;

class RideRequestController extends Controller
{
    public function index()
    {
        return view('ride-requests.index');
    }

    public function show(RideRequest $rideRequest)
    {
        $rideRequest->load([
            'passenger.user',
            'toInstRide.vehicle',
            'fromInstRide.vehicle',
            'passengerLocation',
            'institutionLocation',
            'rideOffers.driver.user',
        ]);

        return view('ride-requests.show', compact('rideRequest'));
    }
}
