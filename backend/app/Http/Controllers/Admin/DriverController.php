<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {

        return view("drivers.index");
    }

    public function show(User $user)
    {
        return view("drivers.show");
    }
}
