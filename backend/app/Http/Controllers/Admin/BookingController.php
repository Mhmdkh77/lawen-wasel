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

        return view("bookings.show",);
    }
}
