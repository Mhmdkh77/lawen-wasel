<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use Illuminate\Http\Request;

class RideController extends Controller
{
    public function index()
    {
        return view("rides.index");
    }

    public function show(Ride $ride)
    {
        return view("rides.show");
    }
}
