<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUser(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'city_id' => 'required|string',
        ]);

        $user = $request->user();

        $city = Location::where('type', 'city')
            ->whereRaw('LOWER(name) = ?', [strtolower($request->city_name)])
            ->first();

        if (!$city) {
            return response()->json(['message' => 'Location Not Supported Yet'], 404);
        }

        $user->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'city_id' => $request->city_id,
        ]);

        return response()->json(['message' => 'Location updated']);
    }

    public function submitLicense(Request $request)
    {
        $request->validate([
            'driver_license' => 'file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $driver = $request->user()->driver;

        if (!$driver) {
            return response()->json(['message' => 'You are not a driver'], 403);
        }

        $path = $request->file('driver_license')?->store('licenses', 'public');

        $driver->update([
            'driver_license' => $path,
        ]);

        return response()->json(['message' => 'Driver license submitted']);
    }
}
