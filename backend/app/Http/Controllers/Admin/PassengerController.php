<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PassengerController extends Controller
{
    public function index()
    {
        return view("passengers.index");
    }

    public function show(User $user)
    {
        return view("passengers.show", ['user' => $user]);
    }
}
