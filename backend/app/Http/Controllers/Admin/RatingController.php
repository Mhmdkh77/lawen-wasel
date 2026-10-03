<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class RatingController extends Controller
{
    public function index()
    {
        return view('ratings.index');
    }
}
