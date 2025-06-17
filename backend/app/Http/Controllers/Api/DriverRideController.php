<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\RideOffer;
use App\Models\RideRequest;
use Illuminate\Http\Request;

class DriverRideController extends Controller
{
    public function rideRequests(Request $request)
    {
        $driver = $request->user()->driver;

        $rideRequests = RideRequest::where('status', 'pending')
            ->where(function ($query) use ($driver) {
                $query->whereHas('toInstRide', function ($q) use ($driver) {
                    $q->whereHas('vehicle', function ($v) use ($driver) {
                        $v->where('driver_id', $driver->id);
                    });
                })->orWhereHas('fromInstRide', function ($q) use ($driver) {
                    $q->whereHas('vehicle', function ($v) use ($driver) {
                        $v->where('driver_id', $driver->id);
                    });
                });
            })
            ->whereDoesntHave('rideOffers', function ($query) use ($driver) {
                $query->where('driver_id', $driver->id);
            })
            ->with(['passenger.user', 'institutionLocation'])
            ->latest()
            ->get();

        return response()->json(['ride_requests' => $rideRequests]);
    }


    public function rideRequest(Request $request, RideRequest $rideRequest)
    {
        $driver = $request->user()->driver;

        $rides = [$rideRequest->to_inst_ride_id, $rideRequest->from_inst_ride_id];

        $ownsRide = Ride::whereIn('id', $rides)
            ->whereHas('vehicle', fn($q) => $q->where('driver_id', $driver->id))
            ->exists();

        if (!$ownsRide) {
            return response()->json(['error' => 'You do not have permission to view this request'], 403);
        }

        return response()->json(['ride_request' => $rideRequest]);
    }

    public function upcomingRide(Request $request)
    {
        $driver = $request->user()->driver;

        $ride = Ride::whereHas('vehicle', function ($q) use ($driver) {
            $q->where('driver_id', $driver->id);
        })
            ->where('scheduled_time', '>', now())
            ->orderBy('scheduled_time')
            ->with(['nodes', 'vehicle'])
            ->first();

        return response()->json(['upcoming_ride' => $ride]);
    }

    public function sendOffer(Request $request)
    {
        $request->validate([
            'ride_request_id' => 'required|exists:ride_requests,id',
            'offered_price' => 'required|numeric|min:0',
            'pickup_location_id' => 'nullable|exists:locations,id',
            'pickup_latitude' => 'nullable|numeric',
            'pickup_longitude' => 'nullable|numeric',
            'pickup_time' => 'nullable|date_format:Y-m-d H:i:s',
            'driver_message' => 'nullable|string',
        ]);

        $driver = $request->user()->driver;

        $rideRequest = RideRequest::findOrFail($request->ride_request_id);

        $offer = RideOffer::create([
            'ride_request_id' => $rideRequest->id,
            'driver_id' => $driver->id,
            'offered_price' => $request->offered_price,
            'suggested_pickup_location_id' => $request->pickup_location_id,
            'suggested_pickup_latitude' => $request->pickup_latitude,
            'suggested_pickup_longitude' => $request->pickup_longitude,
            'pickup_time' => $request->pickup_time,
            'driver_message' => $request->driver_message,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Offer sent successfully', 'offer' => $offer]);
    }
}
