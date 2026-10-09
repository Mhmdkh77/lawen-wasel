<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\RideOffer;
use App\Models\RideRequest;

class BookingStopService
{
    public function nodeAttributes(Ride $ride, RideRequest $rideRequest, ?RideOffer $offer = null): array
    {
        $institution = $rideRequest->institutionLocation;
        $toInstitution = $ride->type === 'to_institution';
        $pickupLocationId = $toInstitution ? $rideRequest->passenger_location_id : $institution->id;
        $pickupLatitude = $toInstitution ? $rideRequest->passenger_latitude : $institution->latitude;
        $pickupLongitude = $toInstitution ? $rideRequest->passenger_longitude : $institution->longitude;

        if ($offer && ($toInstitution || !$rideRequest->to_inst_ride_id)) {
            $suggestedLocation = $offer->suggestedPickupLocation;
            $pickupLocationId = $offer->suggested_pickup_location_id ?? $pickupLocationId;
            $pickupLatitude = $offer->suggested_pickup_latitude ?? $suggestedLocation?->latitude ?? $pickupLatitude;
            $pickupLongitude = $offer->suggested_pickup_longitude ?? $suggestedLocation?->longitude ?? $pickupLongitude;
        }

        return [
            'pickup_location_id' => $pickupLocationId,
            'pickup_latitude' => $pickupLatitude,
            'pickup_longitude' => $pickupLongitude,
            'dropoff_location_id' => $toInstitution ? $institution->id : $rideRequest->passenger_location_id,
            'dropoff_latitude' => $toInstitution ? $institution->latitude : $rideRequest->passenger_latitude,
            'dropoff_longitude' => $toInstitution ? $institution->longitude : $rideRequest->passenger_longitude,
        ];
    }
}
