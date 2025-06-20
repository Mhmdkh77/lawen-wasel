<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationService
{
    public function getCityNameFromCoordinates($lat, $lng)
    {
        $apiKey = config('services.google_maps.api_key');

        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lng&key=$apiKey";

        $response = Http::get($url)->json();

        if (!empty($response['results'])) {
            foreach ($response['results'] as $result) {
                foreach ($result['address_components'] as $component) {
                    if (in_array('locality', $component['types'])) {
                        return $component['long_name']; // e.g. "Jenin"
                    }
                }
            }
        }

        return null;
    }
    public function getOptimizedRoute($ride)
    {
        $ride = $ride->load('nodes', 'driver');

        $driverLat = $ride->driver()->user->latitude;
        $driverLng = $ride->driver()->user->longitude;

        $shipments = [];

        foreach ($ride->nodes as $node) {
            $shipments[] = [
                'pickup' => [
                    'id' => $node->id,
                    'location' => [(float)$node->pickup_longitude, (float)$node->pickup_latitude],
                    'service' => 200
                ],
                'delivery' => [
                    'id' => $node->id,
                    'location' => [(float) $node->dropoff_longitude, (float)$node->dropoff_latitude],
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
            $orderedWaypoints = collect($optimizedRoute['steps'])->map(function ($step) {
                return ['lat' => $step['location'][1], 'lng' => $step['location'][0]];
            })->unique(fn($item) => $item['lat'] . ',' . $item['lng'])->values()->toArray();

            return $orderedWaypoints;
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
