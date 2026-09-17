<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        return view("bookings.index");
    }

    public function show(Booking $booking)
    {
        $booking->load(['passenger.user', 'ride.vehicle', 'ride.rideGroup.driver.user', 'node']);

        return view("bookings.show", compact('booking'));
    }
}
