<?php

namespace App\Http\Controllers\Api;

use App\Models\RideRequest;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class PassengerController extends Controller
{
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

    public function sendRideRequest(Request $request)
    {
        $passenger = $request->user()->passenger;

        if (!$passenger) {
            return response()->json(['message' => 'Only passengers can make ride requests.'], 403);
        }

        // Step 1: Validate
        $data = Validator::make($request->all(), [
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

        return response()->json([
            'message' => 'Ride request submitted successfully.',
            'ride_request' => $rideRequest
        ]);
    }

    public function cancelBooking(Request $request, Booking $booking)
    {
        $passenger = $request->user()->passenger;

        if (!$passenger || $booking->passenger_id !== $passenger->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Prevent canceling if ride already active or completed
        if (in_array($booking->ride->status, ['active', 'completed'])) {
            return response()->json([
                'message' => 'Cannot cancel booking for a ride that has already started or completed.'
            ], 400);
        }

        // Mark booking as canceled
        $booking->update(['status' => 'canceled']);

        return response()->json(['message' => 'Booking canceled successfully.']);
    }

    public function cancelRideRequest(Request $request, RideRequest $rideRequest)
    {
        $passenger = $request->user()->passenger;

        if (!$passenger || $rideRequest->passenger_id !== $passenger->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (in_array($rideRequest->status, ['accepted', 'rejected', 'expired', 'canceled'])) {
            return response()->json(['message' => 'This ride request cannot be canceled.'], 400);
        }

        $rideRequest->update(['status' => 'canceled']);

        return response()->json(['message' => 'Ride request canceled successfully.']);
    }
}
