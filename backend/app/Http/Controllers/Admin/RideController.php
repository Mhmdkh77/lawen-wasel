<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\Ride;
use Illuminate\Support\Facades\Log;
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



        $orderedWaypoints = $locationService->getOptimizedRoute($ride);

        $labeledWaypoints = [];

        foreach ($ride->bookings as $index => $booking) {
            $letter = chr(65 + $index);
            $node = $booking->node;

            $labeledWaypoints[] = [
                'label' => $letter,
                'lat' => (float) $node->pickup_latitude,
                'lng' => (float) $node->pickup_longitude,
                'passenger_name' => $booking->passenger->user->name ?? 'N/A',
            ];
        }

        return view('rides.show', [
            'ride' => $ride,
            'orderedWaypoints' => $orderedWaypoints,
            'labeledWaypoints' => $labeledWaypoints,
        ]);
    }
}
