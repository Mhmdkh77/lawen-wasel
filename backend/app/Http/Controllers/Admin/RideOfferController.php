<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RideOffer;

class RideOfferController extends Controller
{
    public function index()
    {
        return view('ride-offers.index');
    }

    public function show(RideOffer $rideOffer)
    {
        $rideOffer->load([
            'rideRequest.passenger.user',
            'rideRequest.toInstRide',
            'rideRequest.fromInstRide',
            'driver.user',
            'suggestedPickupLocation',
        ]);

        return view('ride-offers.show', compact('rideOffer'));
    }
}
