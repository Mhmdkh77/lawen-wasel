<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Location;
use App\Models\Passenger;
use App\Models\Ride;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {


        return view("index", [
            'users_count' => User::count(),
            'passengers_count' => Passenger::count(),
            'drivers_count' => Driver::count(),
            'rides_count' => Ride::count(),
            'vehicles_count' => Vehicle::count(),
            'bookings_count' => Booking::where('status', 'active')->count(),
            'cities_count' => Location::cities()->count(),
            'stations_count' => Location::stations()->count(),
            'institutions_count' => Location::institutions()->count()
        ]);
    }
}
