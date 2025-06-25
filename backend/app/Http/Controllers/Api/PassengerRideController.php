<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\RideGroup;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\RideRequest;
use App\Models\Ride;
use Illuminate\Support\Facades\Validator;
use App\Models\Booking;
use App\Models\BookingGroup;
use App\Models\Driver;
use App\Models\Node;
use App\Models\RideOffer;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Redis;

class PassengerRideController extends Controller
{
    public function search(Request $request)
    {

        $data = $request->validate([
            'passenger_latitude' => 'required|numeric|between:-90,90',
            'passenger_longitude' => 'required|numeric|between:-180,180',
            'institution_location_id' => 'required|exists:locations,id',
            'arrival_time' => 'required|date',
            'return_time' => 'nullable|date',
            'nb_seats' => 'required|integer|min:1',
        ]);

        // $locationService = new LocationService();
        // $cityName = $locationService->getCityNameFromCoordinates($data['passenger_latitude'], $data['passenger_longitude']);
        // $passengerCity =  Location::where('type', 'city')
        //     ->whereRaw('LOWER(name) = ?', [strtolower($cityName)])
        //     ->first();

        // if (!$passengerCity) {
        //     return response()->json([
        //         'message' => 'City not supported',
        //         'city' => $cityName
        //     ], 422);
        // }

        $arrivalFrom = Carbon::parse($data['arrival_time'])->subHour();
        $arrivalTo = Carbon::parse($data['arrival_time'])->addHour();


        $institutionLocation = Location::findOrFail($data['institution_location_id']);

        $rideGroups = RideGroup::with(['rides' => function ($q) use ($arrivalFrom, $arrivalTo, $data) {
            $q->whereBetween('scheduled_time', [$arrivalFrom, $arrivalTo])
                ->where('type', 'to_institution')
                ->where('available_seats', '>=', $data['nb_seats']);
        }])
            // ->whereHas('locationGroup.locations', function ($q) use ($passengerCity) {
            //     $q->where('location_type', 'passenger')
            //         ->where(function ($q2) use ($passengerCity) {
            //             $q2->where('id', $passengerCity->id)
            //                 ->orWhere('city_id', $passengerCity->city_id);
            //         });
            // })
            ->whereHas('locationGroup.locations', function ($q) use ($institutionLocation) {
                $q->where('location_type', 'institution')
                    ->where(function ($q2) use ($institutionLocation) {
                        $q2->where('locations.id', $institutionLocation->id)
                            ->orWhere('locations.city_id', $institutionLocation->city_id);
                    });
            })->limit(50)
            ->get();


        if (array_key_exists('return_time', $data) && $data['return_time']) {
            $returnFrom = Carbon::parse($data['return_time'])->subHour();
            $returnTo = Carbon::parse($data['return_time'])->addHour();

            $rideGroups = $rideGroups->filter(function ($group) use ($returnFrom, $returnTo, $data) {
                return $group->rides()
                    ->whereBetween('scheduled_time', [$returnFrom, $returnTo])
                    ->where('type', 'from_institution')
                    ->where('available_seats', '>=', $data['nb_seats'])
                    ->exists();
            })->values();
        }

        return response()->json($rideGroups);
    }

    public function showRideGroup(Request $request, RideGroup $rideGroup)
    {
        $passenger = $request->user()?->passenger;

        $requestedRideIds = [];

        if ($passenger) {
            $requestedRideIds = RideRequest::where('passenger_id', $passenger->id)
                ->where(function ($q) use ($rideGroup) {
                    $q->whereIn('to_inst_ride_id', $rideGroup->rides->pluck('id'))
                        ->orWhereIn('from_inst_ride_id', $rideGroup->rides->pluck('id'));
                })
                ->get()
                ->flatMap(function ($request) {
                    return array_filter([
                        $request->to_inst_ride_id,
                        $request->from_inst_ride_id,
                    ]);
                })
                ->unique()
                ->toArray();
        }

        $rideGroup->load([
            'driver.user',
            'rides.vehicle',
        ]);

        $toInstitutionRides = $rideGroup->rides
            ->where('type', 'to_institution')
            ->values();

        $fromInstitutionRides = $rideGroup->rides
            ->where('type', 'from_institution')
            ->values();

        return response()->json([
            'ride_group' => [
                'id' => $rideGroup->id,
                'driver' => [
                    'id' => $rideGroup->driver->id,
                    'name' => $rideGroup->driver->user->name,
                    'email' => $rideGroup->driver->user->email,
                ],
            ],
            'to_institution_rides' => $toInstitutionRides,
            'from_institution_rides' => $fromInstitutionRides,
            'requestedRideIds' => $requestedRideIds,
        ]);
    }

    public function sendRideRequest(Request $request, NotificationService $notificationService)
    {
        $passenger = $request->user()->passenger;

        if (!$passenger) {
            return response()->json(['message' => 'Only passengers can make ride requests.'], 403);
        }

        // Step 1: Validate
        $data = Validator::make($request->all(), [
            'driver_id' => 'required',
            'to_inst_ride_id' => 'nullable|exists:rides,id|required_without:from_inst_ride_id',
            'from_inst_ride_id' => 'nullable|exists:rides,id|required_without:to_inst_ride_id',
            'passenger_latitude' => 'required|numeric|between:-90,90',
            'passenger_longitude' => 'required|numeric|between:-180,180',
            'passenger_location_id' => 'nullable|exists:locations,id',
            'institution_location_id' => 'required|exists:locations,id',
            'nb_seats_requested' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ])->after(function ($validator) use ($request) {
            if (empty($request->to_inst_ride_id) && empty($request->from_inst_ride_id)) {
                $validator->errors()->add('to_inst_ride_id', 'At least one ride must be selected.');
            }
        })->validate();

        // Step 2: Get ride IDs and their scheduled dates
        $rideIds = collect([
            $data['to_inst_ride_id'] ?? null,
            $data['from_inst_ride_id'] ?? null,
        ])->filter();

        $rideDates = Ride::whereIn('id', $rideIds)
            ->pluck('scheduled_time')
            ->map(fn($time) => Carbon::parse($time)->format('Y-m-d'))
            ->unique()
            ->values();

        // Step 3: Prevent duplicate requests for the same rides
        $existingRideIds = RideRequest::where('passenger_id', $passenger->id)
            ->where('status', '!=', 'canceled') // allow retrying canceled requests
            ->get()
            ->flatMap(fn($req) => array_filter([
                $req->to_inst_ride_id,
                $req->from_inst_ride_id,
            ]))
            ->toArray();

        if (
            ($data['to_inst_ride_id'] && in_array($data['to_inst_ride_id'], $existingRideIds)) ||
            ($data['from_inst_ride_id'] && in_array($data['from_inst_ride_id'], $existingRideIds))
        ) {
            return response()->json(['message' => 'You already submitted a request for one of these rides.'], 409);
        }

        // Step 4: Prevent time conflict with other rides
        $existingTimes = RideRequest::where('passenger_id', $passenger->id)
            ->where('status', '!=', 'canceled')
            ->with(['toInstRide', 'fromInstRide'])
            ->get()
            ->flatMap(function ($req) {
                return array_filter([
                    optional($req->toInstRide)->scheduled_time,
                    optional($req->fromInstRide)->scheduled_time,
                ]);
            })
            ->map(fn($time) => Carbon::parse($time)->format('Y-m-d H:i'))
            ->toArray();

        $newRideTimes = Ride::whereIn('id', $rideIds)
            ->pluck('scheduled_time')
            ->map(fn($time) => Carbon::parse($time)->format('Y-m-d H:i'))
            ->toArray();

        $conflict = !empty(array_intersect($existingTimes, $newRideTimes));

        if ($conflict) {
            return response()->json(['message' => 'One of your selected rides conflicts with another you already requested.'], 409);
        }

        // Step 5: Prevent more than one ride request per day (unless rejected)
        $hasSameDayToRequest = $data['to_inst_ride_id']
            ? RideRequest::where('passenger_id', $passenger->id)
            ->whereIn('status', ['pending', 'accepted', 'driver_offered'])
            ->whereHas('toInstRide', function ($q) use ($rideDates) {
                $q->whereIn(DB::raw('DATE(scheduled_time)'), $rideDates);
            })
            ->exists()
            : false;

        if ($hasSameDayToRequest) {
            return response()->json([
                'message' => 'You already have an active ride request for a to_institution ride on this date.'
            ], 409);
        }

        $hasSameDayFromRequest = $data['from_inst_ride_id']
            ? RideRequest::where('passenger_id', $passenger->id)
            ->whereIn('status', ['pending', 'accepted', 'driver_offered'])
            ->whereHas('fromInstRide', function ($q) use ($rideDates) {
                $q->whereIn(DB::raw('DATE(scheduled_time)'), $rideDates);
            })
            ->exists()
            : false;

        if ($hasSameDayFromRequest) {
            return response()->json([
                'message' => 'You already have an active ride request for a from_institution ride on this date.'
            ], 409);
        }

        // Step 6: Determine request type
        $type = (!empty($data['to_inst_ride_id']) && !empty($data['from_inst_ride_id']))
            ? 'round_trip'
            : 'one_way';

        // Step 7: Create the request
        $rideRequest = RideRequest::create([
            'passenger_id' => $passenger->id,
            'to_inst_ride_id' => $data['to_inst_ride_id'] ?? null,
            'from_inst_ride_id' => $data['from_inst_ride_id'] ?? null,
            'passenger_location_id' => $data['passenger_location_id'] ?? null,
            'passenger_latitude' => $data['passenger_latitude'],
            'passenger_longitude' => $data['passenger_longitude'],
            'institution_location_id' => $data['institution_location_id'],
            'nb_seats_requested' => $data['nb_seats_requested'],
            'notes' => $data['notes'] ?? null,
            'type' => $type,
            'status' => 'pending',
        ]);

        // $deviceToken = Driver::where('id', $data['driver_id'])->first();

        // if ($deviceToken) {
        //     $notificationService->sendPush(
        //         $deviceToken,
        //         'New Ride Request',
        //         'Your have new ride request',
        //         [
        //             'ride_request_id' => $rideRequest->id,
        //             'status' => 'ended'
        //         ]
        //     );
        // }

        return response()->json([
            'message' => 'Ride request submitted successfully.',
            'ride_request' => $rideRequest
        ]);
    }

    public function getRideRequests(Request $request)
    {
        $passenger = $request->user()->passenger;

        if (!$passenger) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $requests = RideRequest::with([
            'toInstRide.vehicle',
            'toInstRide.rideGroup.driver.user',
            'fromInstRide.vehicle',
            'fromInstRide.rideGroup.driver.user',
        ])
            ->where('passenger_id', $passenger->id)
            ->whereIn('status', ['pending', 'driver_offered'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['ride_requests' => $requests]);
    }

    public function getRideRequest(Request $request, RideRequest $rideRequest)
    {
        $passenger = $request->user()->passenger;

        if ($rideRequest->passenger_id  != $passenger->id) {
            return response()->json(['error' => 'You do not have permission to view this Ride Offer'], 403);
        }

        $rideRequest->load(['fromInstRide', 'toInstRide', 'passengerLocation', 'institutionLocation', 'rideOffers']);

        return response()->json($rideRequest);
    }

    public function cancelRideRequest(Request $request, RideRequest $rideRequest, NotificationService $notificationService)
    {
        $passenger = $request->user()->passenger;

        if (!$passenger || $rideRequest->passenger_id !== $passenger->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (in_array($rideRequest->status, ['accepted', 'rejected', 'expired', 'canceled', 'driver_offered'])) {
            return response()->json(['message' => 'This ride request cannot be canceled.'], 400);
        }

        $rideRequest->update(['status' => 'canceled']);

        $rideRequest->load([
            'toInstRide.vehicle.driver',
            'fromInstRide.vehicle.driver'
        ]);

        $drivers = collect([
            optional($rideRequest->toInstRide->vehicle)->driver,
            optional($rideRequest->fromInstRide->vehicle)->driver,
        ])->filter()->unique('id');

        foreach ($drivers as $driver) {
            // if ($driver->device_token) {
            //     $notificationService->sendPush(
            //         $driver->device_token,
            //         'Ride Request Canceled',
            //         'A passenger has canceled a ride request.',
            //         [
            //             'ride_request_id' => $rideRequest->id,
            //             'status' => 'canceled'
            //         ]
            //     );
            // }
        }

        return response()->json(['message' => 'Ride request canceled successfully.']);
    }

    public function getRideOffers(Request $request)
    {
        $passenger = $request->user()->passenger;

        $rideOffers = RideOffer::whereHas('rideRequest', function ($q) use ($passenger) {
            $q->where('passenger_id', $passenger->id);
        })->with(['rideRequest', 'rideRequest.toInstRide', 'rideRequest.fromInstRide', 'driver'])->orderByDesc('created_at')
            ->get();

        return response()->json($rideOffers);
    }

    public function getRideOffer(Request $request, RideOffer $rideOffer)
    {
        $passenger = $request->user()->passenger;

        // Eager load related models first
        $rideOffer->load(['driver', 'rideRequest.passenger', 'suggestedPickupLocation']);

        if (!$rideOffer->rideRequest || !$rideOffer->rideRequest->passenger || $rideOffer->rideRequest->passenger->id !== $passenger->id) {
            return response()->json(['error' => 'You do not have permission to view this Ride Offer'], 403);
        }

        return response()->json($rideOffer);
    }

    public function acceptRideOffer(Request $request, RideOffer $rideOffer, NotificationService $notificationService)
    {
        $passenger = $request->user()->passenger;

        $rideOffer->load([
            'driver',
            'rideRequest.passenger',
            'rideRequest.institutionLocation',
            'rideRequest.toInstRide',
            'rideRequest.fromInstRide',
            'suggestedPickupLocation',
        ]);

        if (
            !$rideOffer->rideRequest ||
            !$rideOffer->rideRequest->passenger ||
            $rideOffer->rideRequest->passenger->id !== $passenger->id
        ) {
            return response()->json(['error' => 'You do not have permission to accept this Ride Offer'], 403);
        }

        if ($rideOffer->status !== 'pending') {
            return response()->json(['error' => 'This offer has already been responded to.'], 400);
        }

        DB::transaction(function () use ($rideOffer, $passenger, $notificationService) {
            // Update offer status
            $rideOffer->update(['status' => 'accepted']);

            // Create booking group (one per passenger per accepted offer)
            $bookingGroup = BookingGroup::create([
                'passenger_id' => $passenger->id,
            ]);

            $institutionLocation = $rideOffer->rideRequest->institutionLocation;

            $createBookingAndNode = function ($ride, $type) use ($bookingGroup, $rideOffer, $passenger, $institutionLocation) {
                if (!$ride) {
                    return null;
                }


                // Seats requested
                $seats = $rideOffer->rideRequest->nb_seats_requested;

                // Check availability
                if ($ride->available_seats < $seats) {
                    throw new \Exception("Not enough available seats in ride ID {$ride->id}");
                }


                // Create Node for the ride
                $node = Node::create([
                    'ride_id' => $ride->id,
                    'pickup_location_id' => $rideOffer->suggested_pickup_location_id,
                    'pickup_latitude' => $rideOffer->suggested_pickup_latitude ?? 0,
                    'pickup_longitude' => $rideOffer->suggested_pickup_longitude ?? 0,
                    'dropoff_location_id' => $institutionLocation->id,
                    'dropoff_latitude' => $institutionLocation->latitude ?? 0,
                    'dropoff_longitude' => $institutionLocation->longitude ?? 0,
                    'status' => 'pending',
                ]);

                $ride->increment('booked_seats', $seats);
                $ride->decrement('available_seats', $seats);

                // Create Booking for this ride
                return Booking::create([
                    'passenger_id' => $passenger->id,
                    'ride_id' => $ride->id,
                    'booking_group_id' => $bookingGroup->id,
                    'ride_request_id' => $rideOffer->rideRequest->id,
                    'node_id' => $node->id,
                    'nb_seats' => $rideOffer->rideRequest->nb_seats_requested,
                    'price' => $rideOffer->offered_price,
                    'status' => 'active',
                ]);
            };

            // Create booking & node for to_institution ride (if present)
            $toBooking = $createBookingAndNode($rideOffer->rideRequest->toInstRide, 'to_institution');

            // Create booking & node for from_institution ride (if present)
            $fromBooking = $createBookingAndNode($rideOffer->rideRequest->fromInstRide, 'from_institution');

            // Notify driver
            $driver = $rideOffer->driver;
            // if ($driver && $driver->device_token) {
            //     $notificationService->sendPush(
            //         $driver->device_token,
            //         'Ride Offer Accepted',
            //         'A passenger has accepted your ride offer.',
            //         [
            //             'ride_offer_id' => $rideOffer->id,
            //             'status' => 'accepted',
            //         ]
            //     );
            // }
        });

        return response()->json(['message' => 'Ride offer accepted and bookings created successfully.']);
    }

    public function rejectRideOffer(Request $request, RideOffer $rideOffer, NotificationService $notificationService)
    {
        $passenger = $request->user()->passenger;

        $rideOffer->load(['driver', 'rideRequest.passenger', 'suggestedPickupLocation']);

        if (
            !$rideOffer->rideRequest ||
            !$rideOffer->rideRequest->passenger ||
            $rideOffer->rideRequest->passenger->id !== $passenger->id
        ) {
            return response()->json(['error' => 'You do not have permission to reject this Ride Offer'], 403);
        }

        if ($rideOffer->status !== 'pending') {
            return response()->json(['error' => 'This offer has already been responded to.'], 400);
        }

        $rideOffer->update(['status' => 'rejected']);

        $driver = $rideOffer->driver;
        // if ($driver && $driver->device_token) {
        //     $notificationService->sendPush(
        //         $driver->device_token,
        //         'Ride Offer Rejected',
        //         'A passenger has rejected your ride offer.',
        //         [
        //             'ride_offer_id' => $rideOffer->id,
        //             'status' => 'rejected',
        //         ]
        //     );
        // }

        return response()->json(['message' => 'Ride offer rejected successfully.']);
    }

    public function getBookings(Request $request)
    {
        $passenger = $request->user()->passenger;

        if (!$passenger) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bookings = Booking::with([
            'ride.vehicle',
            'ride.rideGroup.driver.user',
        ])
            ->where('passenger_id', $passenger->id)
            ->whereHas('ride', function ($q) {
                $q->whereIn('status', ['pending', 'active']);
            })
            ->orderByDesc('created_at')
            ->get();

        $response = $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'nb_seats' => $booking->nb_seats,
                'price' => $booking->price,
                'ride' => [
                    'id' => $booking->ride->id,
                    'scheduled_time' => $booking->ride->scheduled_time,
                    'status' => $booking->ride->status,
                    'vehicle' => $booking->ride->vehicle,
                    'driver' => [
                        'id' => $booking->ride->rideGroup->driver->id,
                        'name' => $booking->ride->rideGroup->driver->user->name,
                    ]
                ]
            ];
        });

        return response()->json(['bookings' => $response]);
    }

    public function getBooking(Request $request, Booking $booking)
    {
        $passenger = $request->user()->passneger;

        if ($booking->passenger_id !== $passenger->id) {
            return response()->json(['error' => 'You do not have permission'], 403);
        }

        $booking->load(['passenger', 'ride.driver', 'node']);


        return response()->json($booking);
    }

    public function cancelBooking(Request $request, Booking $booking, NotificationService $notificationService)
    {
        $passenger = $request->user()->passenger;

        if (!$passenger || $booking->passenger_id !== $passenger->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (in_array($booking->ride->status, ['active', 'completed'])) {
            return response()->json([
                'message' => 'Cannot cancel booking for a ride that has already started or completed.'
            ], 400);
        }

        DB::transaction(function () use ($booking, $notificationService) {
            $ride = $booking->ride;
            $seats = $booking->nb_seats;

            $booking->update(['status' => 'passenger_canceled']);

            if ($booking->node) {
                $booking->node->delete();
            }

            $ride->decrement('booked_seats', $seats);
            $ride->increment('available_seats', $seats);

            $driver = $booking->ride->vehicle->driver ?? null;
            // if ($driver && $driver->device_token) {
            //     $notificationService->sendPush(
            //         $driver->device_token,
            //         'Booking Canceled',
            //         'A passenger has canceled their booking.',
            //         [
            //             'booking_id' => $booking->id,
            //             'status' => 'passenger_canceled',
            //         ]
            //     );
            // }
        });

        return response()->json(['message' => 'Booking canceled successfully.']);
    }
}
