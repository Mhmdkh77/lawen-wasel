<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function index()
    {
        return view("stations.index");
    }

    public function show(Station $station)
    {
        return view("stations.show");
    }
}
