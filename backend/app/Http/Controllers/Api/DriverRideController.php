<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\RideOffer;
use App\Models\RideRequest;
use App\Services\NotificationService;
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
        $rideRequest->load(['passenger.user', 'ride', 'toInstRide', 'fromInstRide']);

        return response()->json([
            'ride_request' => $rideRequest
        ]);
    }

    public function rejectRideRequest(Request $request, RideRequest $rideRequest, NotificationService $notificationService)
    {
        $driver = $request->user()->driver;

        $rides = [$rideRequest->to_inst_ride_id, $rideRequest->from_inst_ride_id];

        $ownsRide = Ride::whereIn('id', $rides)
            ->whereHas('vehicle', fn($q) => $q->where('driver_id', $driver->id))
            ->exists();

        if (!$ownsRide) {
            return response()->json(['error' => 'You do not have permission to reject this request'], 403);
        }

        $rideRequest->update([
            'status' => 'rejected'
        ]);

        $passenger = $rideRequest->passenger;
        $deviceToken = $passenger?->user?->device_token;

        if ($deviceToken) {
            $notificationService->sendPush(
                $deviceToken,
                'Ride Request Rejected',
                'Your ride request was rejected by the driver.',
                [
                    'ride_request_id' => $rideRequest->id,
                    'status' => 'rejected'
                ]
            );
        }

        return response()->json(['message' => 'Ride Request Rejected']);
    }

    public function rideOffers(Request $request)
    {
        $driver = $request->user()->driver;

        $rideOffers = RideOffer::where('driver_id', $driver->id)->with(['rideRequest.passenger.user'])->latest()->paginate(20);;

        return response()->json($rideOffers);
    }

    public function sendOffer(Request $request, NotificationService $notificationService)
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

        $passenger = $rideRequest->passenger;
        $deviceToken = $passenger?->user?->device_token;

        if ($deviceToken) {
            $notificationService->sendPush(
                $deviceToken,
                'New Ride Offer',
                'You have new ride offer.',
                [
                    'ride_offer_id' => $offer->id,
                    'status' => 'offered'
                ]
            );
        }

        return response()->json(['message' => 'Offer sent successfully', 'offer' => $offer]);
    }

    public function rideOffer(Request $request, RideOffer $rideOffer)
    {
        $driver = $request->user()->driver;

        if ($rideOffer->driver_id  != $driver->id) {
            return response()->json(['error' => 'You do not have permission to view this Ride Offer'], 403);
        }

        $rideOffer->load(['rideRequest.passenger.user', 'rideRequest.toInstRide', 'rideRequest.fromInstRide']);

        return  response()->json($rideOffer);
    }

    public function editRideOffer(Request $request, RideOffer $rideOffer)
    {
        $driver = $request->user()->driver;

        if ($rideOffer->driver_id  != $driver->id) {
            return response()->json(['error' => 'You do not have permission to view this Ride Offer'], 403);
        }
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
}
