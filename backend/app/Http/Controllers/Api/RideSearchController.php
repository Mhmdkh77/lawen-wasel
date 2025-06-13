<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\RideGroup;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RideSearchController extends Controller
{

    public function seach(Request $request)
    {
        $data = $request->validate([
            'passenger_latitude' => 'required|numeric|between:-90,90',
            'passenger_longitude' => 'required|numeric|between:-180,180',
            'institution_location_id' => 'required|exists:locations,id',
            'arrival_time' => 'required|date',
            'return_time' => 'nullable|date',
            'nb_seats' => 'required|integer|min:1',
        ]);

        $locationService = new LocationService();
        $cityName = $locationService->getCityNameFromCoordinates($data['passenger_latitude'], $data['passenger_longitude']);
        $passengerCity =  Location::where('type', 'city')
            ->whereRaw('LOWER(name) = ?', [strtolower($cityName)])
            ->first();

        if (!$passengerCity) {
            return response()->json(['message' => 'City not supported'], 422);
        }

        $arrivalFrom = Carbon::parse($data['arrival_time'])->subHour();
        $arrivalTo = Carbon::parse($data['arrival_time'])->addHour();


        $institutionLocation = Location::findOrFail($data['institution_location_id']);

        $rideGroups = RideGroup::with(['rides' => function ($q) use ($arrivalFrom, $arrivalTo, $data) {
            $q->whereBetween('scheduled_time', [$arrivalFrom, $arrivalTo])
                ->where('type', 'to_institution')
                ->where('available_seats', '>=', $data['nb_seats']);
        }])
            ->whereHas('locationGroup.locations', function ($q) use ($passengerCity) {
                $q->where('location_type', 'passenger')
                    ->where(function ($q2) use ($passengerCity) {
                        $q2->where('id', $passengerCity->id)
                            ->orWhere('city_id', $passengerCity->city_id);
                    });
            })
            ->whereHas('locationGroup.locations', function ($q) use ($institutionLocation) {
                $q->where('location_type', 'institution')
                    ->where(function ($q2) use ($institutionLocation) {
                        $q2->where('id', $institutionLocation->id)
                            ->orWhere('city_id', $institutionLocation->city_id);
                    });
            })
            ->get();


        if ($data['return_time']) {
            $returnFrom = Carbon::parse($data['return_time'])->subHour();
            $returnTo = Carbon::parse($data['return_time'])->addHour();

            foreach ($rideGroups as $group) {
                $group->return_rides = $group->rides()
                    ->where('type', 'from_institution')
                    ->whereBetween('scheduled_time', [$returnFrom, $returnTo])
                    ->where('available_seats', '>=', $data['nb_seats'])
                    ->get();
            }
        }

        return response()->json($rideGroups);
    }
}
