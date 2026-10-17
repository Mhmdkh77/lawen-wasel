<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use App\Services\DriverLicenseStorage;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        return view("drivers.index");
    }

    public function show(User $user)
    {

        if ($user->role === 'passenger') {
            abort(404, 'Driver not found.');
        }

        $user->load(['city', 'driver.vehicles']);

        return view("drivers.show", ['user' => $user]);
    }

    public function toggleVerification(Driver $driver)
    {
        $driver->is_verified = !$driver->is_verified;
        $driver->save();

        return response()->json(['status' => $driver->is_verified]);
    }

    public function license(User $user, DriverLicenseStorage $storage)
    {
        abort_unless($user->role === 'driver', 404);

        return $storage->response($user->driver?->driver_license);
    }
}
