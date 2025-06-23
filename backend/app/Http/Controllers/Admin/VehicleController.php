<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        return view("vehicles.index");
    }

    public function show(Vehicle $vehicle)
    {

        return view("vehicles.show",);
    }
}
