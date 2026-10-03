<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Services\LocationService;

class RideController extends Controller
{

    public function index()
    {
        return view("rides.index");
    }

    public function show(Ride $ride, LocationService $locationService)
    {
        $ride->load([
            'vehicle.driver.user',
            'bookings.passenger.user',
            'bookings.node'
        ]);



        $routePlan = $locationService->getRoutePlan($ride);

        return view('rides.show', [
            'ride' => $ride,
            'orderedWaypoints' => $routePlan['waypoints'],
            'routeCheckpoints' => $routePlan['checkpoints'],
            'routeOptimized' => $routePlan['optimized'],
        ]);
    }
}
