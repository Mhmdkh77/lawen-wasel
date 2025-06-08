<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\Ride;
use Illuminate\Support\Facades\Log; // Import the Log facade

class RideController extends Controller
{

    public function index()
    {
        return view("rides.index");
    }

    public function show(Ride $ride)
    {
        $ride = $ride->load('nodes.destination', 'driver');

        $driverLat = $ride->driver->latitude;
        $driverLng = $ride->driver->longitude;

        $shipments = [];

        foreach ($ride->nodes->where('type', 'pickup') as $node) {
            $shipments[] = [
                'pickup' => [
                    'id' => $node->id,
                    'location' => [(float)$node->longitude, (float)$node->latitude],
                    'service' => 200
                ],
                'delivery' => [
                    'id' => $node->id,
                    'location' => [(float) $node->destination->longitude, (float)$node->destination->latitude],
                    'service' => 200
                ]
            ];
        }

        $payload = [
            'vehicles' => [
                [
                    'id' => $ride->driver->id,
                    'start' => [(float) $driverLng, (float) $driverLat],
                    'profile' => 'driving-car',
                ]
            ],
            'shipments' => $shipments
        ];

        $orsApiKey = env('ORS_API_KEY');

        try {
            $response = Http::withHeaders([
                'Authorization' => $orsApiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('https://api.openrouteservice.org/optimization', $payload);

            $data = $response->json();

            $optimizedRoute = $data['routes'][0] ?? null;


            $coordinates = collect($optimizedRoute['steps'])
                ->pluck('location')
                ->values()
                ->toArray();
            $directionsUrl = "https://api.openrouteservice.org/v2/directions/driving-car?api_key=" . env('ORS_API_KEY');

            $directionsResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($directionsUrl, [
                'coordinates' => $coordinates // [[lng, lat], [lng, lat], ...]
            ]);

            $geometry = optional($directionsResponse->json())['routes'][0]['geometry'] ?? [];
            return view('rides.show', [
                'ride' => $ride,
                'geometry' => $geometry,  // encoded polyline string from ORS directions
                'orderedWaypoints' => collect($optimizedRoute['steps'])->map(function ($step) {
                    return ['lat' => $step['location'][1], 'lng' => $step['location'][0]];
                })->values()->toArray(),
            ]);
        } catch (\Exception $e) {
            // Catch all exceptions for comprehensive logging
            Log::error('Exception caught during ORS optimization for ride ID: ' . $ride->id . ': ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'An unexpected error occurred during route optimization.');
        }
    }
}
