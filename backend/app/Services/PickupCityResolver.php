<?php

namespace App\Services;

use App\Models\Location;

class PickupCityResolver
{
    private const MAX_DISTANCE_KM = 10.0;

    public function nearestSupportedCity(float $latitude, float $longitude): ?Location
    {
        $nearest = null;
        $nearestDistance = self::MAX_DISTANCE_KM;

        foreach (Location::cities()->get(['id', 'latitude', 'longitude']) as $city) {
            $distance = $this->distanceKm($latitude, $longitude, (float) $city->latitude, (float) $city->longitude);
            if ($distance <= $nearestDistance) {
                $nearest = $city;
                $nearestDistance = $distance;
            }
        }

        return $nearest;
    }

    private function distanceKm(float $latitude, float $longitude, float $cityLatitude, float $cityLongitude): float
    {
        $latitudeDifference = deg2rad($cityLatitude - $latitude);
        $longitudeDifference = deg2rad($cityLongitude - $longitude);
        $arc = sin($latitudeDifference / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad($cityLatitude)) * sin($longitudeDifference / 2) ** 2;

        return 6371.0 * 2 * asin(min(1.0, sqrt($arc)));
    }
}
