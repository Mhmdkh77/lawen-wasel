<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RideTemplateGroup;

class RideTemplateGroupController extends Controller
{
    public function index()
    {
        return view('ride-template-groups.index');
    }

    public function show(RideTemplateGroup $rideTemplateGroup)
    {
        $rideTemplateGroup->load([
            'driver.user',
            'locationGroup.locations',
            'rideTemplates.vehicle',
        ]);

        return view('ride-template-groups.show', compact('rideTemplateGroup'));
    }
}
