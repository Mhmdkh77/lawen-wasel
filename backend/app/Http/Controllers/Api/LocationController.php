<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{


    public function getLocation(Request $request)
    {
        $cities = Location::where("type", 'city')->get();
        $stations = Location::where("type", 'station')->get();
        $institutions = Location::where("type", 'institution')->get();

        return response()->json([
            'cities' => $cities,
            'stations' => $stations,
            'institutions' => $institutions
        ]);
    }
}
