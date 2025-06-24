<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LocationGroup;
use App\Models\LocationGroupLocationRel;
use App\Models\Ride;
use App\Models\RideGroup;
use App\Models\RideOffer;
use App\Models\RideRequest;
use App\Models\Vehicle;
use App\Services\NotificationService;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // $deviceToken = $passenger?->user?->device_token;

        // if ($deviceToken) {
        //     $notificationService->sendPush(
        //         $deviceToken,
        //         'Ride Request Rejected',
        //         'Your ride request was rejected by the driver.',
        //         [
        //             'ride_request_id' => $rideRequest->id,
        //             'status' => 'rejected'
        //         ]
        //     );
        // }

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
        // $deviceToken = $passenger?->user?->device_token;

        // if ($deviceToken) {
        //     $notificationService->sendPush(
        //         $deviceToken,
        //         'New Ride Offer',
        //         'You have new ride offer.',
        //         [
        //             'ride_offer_id' => $offer->id,
        //             'status' => 'offered'
        //         ]
        //     );
        // }

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


    public function getRides(Request $request)
    {
        $driver = $request->user()->driver;

        $ride_groups = RideGroup::where('driver_id', $driver->id)->with(['rides.vehicle', 'locationGroup.locations'])->get();

        return response()->json($ride_groups);
    }

    public function createRide(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'scheduled_time' => 'required|date_format:H:i',
            'type' => 'required|in:to_institution,from_institution',
            'locations' => 'required|array|min:1',
            'locations.*.id' => 'required|exists:locations,id',
            'locations.*.type' => 'required|in:passenger,institution',
        ]);

        $driver = $request->user()->driver;

        $vehicle = Vehicle::where('driver_id', $driver->id)->where('id', $request->vehicle_id)->first();

        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found or does not belong to the driver'], 403);
        }

        try {
            DB::transaction(function () use ($request, $driver) {
                $locationGroup = LocationGroup::create();

                foreach ($request->locations as $loc) {
                    LocationGroupLocationRel::create([
                        'location_group_id' => $locationGroup->id,
                        'location_id' => $loc['id'],
                        'location_type' => $loc['type'],
                    ]);
                }

                $rideGroup = RideGroup::create([
                    'driver_id' => $driver->id,
                    'location_group_id' => $locationGroup->id,
                ]);


                Ride::create([
                    'vehicle_id' => $request['vehicle_id'],
                    'ride_group_id' => $rideGroup->id,
                    'scheduled_time' => $request['scheduled_time'],
                    'type' => $request['type'],
                ]);
            });

            return response()->json(['message' => 'Ride created successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create ride', 'error' => $e->getMessage()], 500);
        }
    }

    public function getRide(Request $request, Ride $ride, LocationService $locationService)
    {
        $driver = $request->user()->driver;

        if ($ride->vehicle->driver_id !== $driver->id) {
            return response()->json(['error' => 'You do not have permission to start this ride'], 403);
        }

        $route = $locationService->getOptimizedRoute($ride);

        $ride->load(['vehicle.driver', 'nodes', 'passengers', 'locations', 'bookings.node']);



        return response()->json([
            'ride' => $ride,
            'route' => $route,
        ]);
    }


    public function updateRide(Request $request, Ride $ride, LocationService $locationService)
    {
        $driver = $request->user()->driver;

        if ($ride->vehicle->driver_id !== $driver->id) {
            return response()->json(['error' => 'You do not have permission to start this ride'], 403);
        }
    }

    public function startRide(Request $request, Ride $ride, NotificationService $notificationService)
    {
        $driver = $request->user()->driver;

        if ($ride->vehicle->driver_id !== $driver->id) {
            return response()->json(['error' => 'You do not have permission to start this ride'], 403);
        }

        if ($ride->status !== 'pending') {
            return response()->json(['error' => 'Ride cannot be started'], 400);
        }

        $ride->update([
            'status' => 'active',
            'start_time' => now()
        ]);

        $ride->load([['passengers']]);

        foreach ($ride->passengers() as $passenger) {
            // $deviceToken = $passenger?->user?->device_token;

            // if ($deviceToken) {
            //     $notificationService->sendPush(
            //         $deviceToken,
            //         'Ride Started',
            //         'Your ride has started',
            //         [
            //             'ride_id' => $ride->id,
            //             'status' => 'started'
            //         ]
            //     );
            // }
        }



        return response()->json([
            'message' => 'Ride started successfully',
            'ride' => $ride
        ]);
    }

    public function finishtRide(Request $request, Ride $ride, NotificationService $notificationService)
    {

        $driver = $request->user()->driver;

        if ($ride->vehicle->driver_id !== $driver->id) {
            return response()->json(['error' => 'You do not have permission to edit this ride'], 403);
        }

        if ($ride->status !== 'active') {
            return response()->json(['error' => 'Ride cannot be completed'], 400);
        }

        $ride->update([
            'status' => 'completed',
            'finish_time' => now()
        ]);

        $ride->load([['passengers']]);

        foreach ($ride->passengers() as $passenger) {
            // $deviceToken = $passenger?->user?->device_token;

            // if ($deviceToken) {
            //     $notificationService->sendPush(
            //         $deviceToken,
            //         'Ride Ended',
            //         'Your ride has ended',
            //         [
            //             'ride_id' => $ride->id,
            //             'status' => 'ended'
            //         ]
            //     );
            // }
        }

        return response()->json([
            'message' => 'Ride ended successfully',
            'ride' => $ride
        ]);
    }
}
