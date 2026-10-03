<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
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
        $ride = $ride->load('nodes', 'driver.user');

        $driverLat = $ride->driver?->user?->latitude;
        $driverLng = $ride->driver?->user?->longitude;

        if ($driverLat === null || $driverLng === null) {
            return [];
        }

        $shipments = [];
        $fallbackWaypoints = [[
            'lat' => (float) $driverLat,
            'lng' => (float) $driverLng,
        ]];

        if ($ride->nodes->isEmpty()) {
            return $fallbackWaypoints;
        }

        foreach ($ride->nodes as $node) {
            $fallbackWaypoints[] = [
                'lat' => (float) $node->pickup_latitude,
                'lng' => (float) $node->pickup_longitude,
            ];
            $fallbackWaypoints[] = [
                'lat' => (float) $node->dropoff_latitude,
                'lng' => (float) $node->dropoff_longitude,
            ];

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

        $orsApiKey = config('services.ors.key');

        if (!$orsApiKey) {
            return $fallbackWaypoints;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $orsApiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->connectTimeout(2)->timeout(5)
                ->post('https://api.openrouteservice.org/optimization', $payload);

            if (!$response->successful()) {
                Log::warning('Route optimization unavailable', ['ride_id' => $ride->id, 'status' => $response->status()]);
                return $fallbackWaypoints;
            }

            $steps = data_get($response->json(), 'routes.0.steps');

            if (!is_array($steps) || $steps === []) {
                Log::warning('Route optimization returned no route', ['ride_id' => $ride->id]);
                return $fallbackWaypoints;
            }

            $orderedWaypoints = collect($steps)
                ->filter(fn($step) => isset($step['location'][0], $step['location'][1])
                    && is_numeric($step['location'][0]) && is_numeric($step['location'][1]))
                ->map(fn($step) => [
                    'lat' => (float) $step['location'][1],
                    'lng' => (float) $step['location'][0],
                ])
                ->unique(fn($point) => $point['lat'] . ',' . $point['lng'])
                ->values()
                ->toArray();

            return count($orderedWaypoints) >= 2 ? $orderedWaypoints : $fallbackWaypoints;
        } catch (ConnectionException $e) {
            Log::warning('Route optimization connection failed', ['ride_id' => $ride->id, 'error' => $e->getMessage()]);
            return $fallbackWaypoints;
        }
    }
}
