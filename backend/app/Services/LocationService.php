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
    public function getOptimizedRoute($ride): array
    {
        return $this->getRoutePlan($ride)['waypoints'];
    }

    public function getRoutePlan($ride): array
    {
        $ride->loadMissing('nodes', 'driver.user.city', 'bookings.passenger.user', 'bookings.node.pickupLocation', 'bookings.node.dropoffLocation');

        $driverLat = $ride->driver?->user?->latitude;
        $driverLng = $ride->driver?->user?->longitude;

        if ($driverLat === null || $driverLng === null) {
            return ['waypoints' => [], 'checkpoints' => [], 'optimized' => false];
        }

        $start = [
            'number' => 0,
            'kind' => 'start',
            'lat' => (float) $driverLat,
            'lng' => (float) $driverLng,
            'label' => 'Driver start',
            'place' => $ride->driver->user->city?->name ?? 'Current location',
        ];
        $nodes = $ride->nodes->sortBy('id')->values();
        $bookingsByNode = $ride->bookings->keyBy('node_id');
        $fallbackActions = $nodes->flatMap(fn($node) => [
            ['kind' => 'pickup', 'node' => $node],
            ['kind' => 'delivery', 'node' => $node],
        ])->all();
        $fallbackPlan = $this->buildRoutePlan($start, $fallbackActions, $bookingsByNode, false);

        if ($nodes->isEmpty()) {
            return $fallbackPlan;
        }

        $shipments = [];

        foreach ($nodes as $node) {
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
            return $fallbackPlan;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $orsApiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->connectTimeout(8)->timeout(15)
                ->post(config('services.ors.optimization_url'), $payload);

            if (!$response->successful()) {
                Log::warning('Route optimization unavailable', ['ride_id' => $ride->id, 'status' => $response->status()]);
                return $fallbackPlan;
            }

            $steps = data_get($response->json(), 'routes.0.steps');

            if (!is_array($steps) || $steps === []) {
                Log::warning('Route optimization returned no route', ['ride_id' => $ride->id]);
                return $fallbackPlan;
            }

            $orderedSteps = collect($steps)
                ->filter(fn($step) => in_array($step['type'] ?? null, ['pickup', 'delivery'], true))
                ->values();
            $expected = $nodes->flatMap(fn($node) => ['pickup:' . $node->id, 'delivery:' . $node->id])->sort()->values()->all();
            $actual = $orderedSteps->map(fn($step) => $step['type'] . ':' . ($step['id'] ?? ''))->sort()->values()->all();

            if ($actual !== $expected) {
                Log::warning('Route optimization omitted stops', ['ride_id' => $ride->id]);
                return $fallbackPlan;
            }

            $nodesById = $nodes->keyBy('id');
            $actions = $orderedSteps->map(fn($step) => [
                'kind' => $step['type'],
                'node' => $nodesById->get($step['id']),
            ])->all();

            return $this->buildRoutePlan($start, $actions, $bookingsByNode, true);
        } catch (ConnectionException $e) {
            Log::warning('Route optimization connection failed', ['ride_id' => $ride->id, 'error' => $e->getMessage()]);
            return $fallbackPlan;
        }
    }

    private function buildRoutePlan(array $start, array $actions, $bookingsByNode, bool $optimized): array
    {
        $checkpoints = [$start];

        foreach ($actions as $action) {
            $node = $action['node'];
            $pickup = $action['kind'] === 'pickup';
            $booking = $bookingsByNode->get($node->id);
            $passenger = $booking?->passenger?->user?->name;
            $place = $pickup ? $node->pickupLocation?->name : $node->dropoffLocation?->name;

            $checkpoints[] = [
                'number' => count($checkpoints),
                'kind' => $action['kind'],
                'node_id' => $node->id,
                'lat' => (float) ($pickup ? $node->pickup_latitude : $node->dropoff_latitude),
                'lng' => (float) ($pickup ? $node->pickup_longitude : $node->dropoff_longitude),
                'label' => ($pickup ? 'Pick up' : 'Drop off') . ($passenger ? ' ' . $passenger : ' passenger'),
                'place' => $place ?? ($pickup ? 'Custom pickup' : 'Custom drop-off'),
            ];
        }

        $waypoints = [];
        foreach ($checkpoints as $checkpoint) {
            $point = ['lat' => $checkpoint['lat'], 'lng' => $checkpoint['lng']];
            if ($waypoints === [] || end($waypoints) !== $point) {
                $waypoints[] = $point;
            }
        }

        return ['waypoints' => $waypoints, 'checkpoints' => $checkpoints, 'optimized' => $optimized];
    }
}
