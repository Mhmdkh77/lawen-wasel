<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Services\LocationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function getUser(Request $request)
    {
        $user = $request->user()->fresh();

        if ($user->role === 'driver') {
            $user->load('driver.vehicles');

            $driverLicenseUrl = $user->driver && $user->driver->driver_license_path
                ? Storage::url($user->driver->driver_license_path)
                : null;
        } elseif ($user->role === 'passenger') {
            $user->load('passenger');
            $driverLicenseUrl = null;
        } else {
            $driverLicenseUrl = null;
        }

        return response()->json([
            'user' => $user,
            'image' => $user->image ? Storage::url($user->image) : null,
            'driver_license_url' => $driverLicenseUrl,
        ]);
    }

    public function updateUser(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|unique:users,phone,' . $user->id,
            'gender' => 'sometimes|in:male,female',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ];

        if ($user->role === 'driver') {
            $rules['default_vehicle_id'] = 'sometimes|nullable|exists:vehicles,id';

            // Only allow updating driver_license file if NOT verified
            if (!($user->driver && $user->driver->is_verified)) {
                $rules['driver_license'] = 'sometimes|file|mimes:jpeg,png,jpg,pdf|max:5120'; // adjust mime and size as needed
            }
        }

        $data = $request->validate($rules);

        if (isset($data['default_vehicle_id'])) {
            $vehicleBelongsToDriver = $user->driver->vehicles()->where('id', $data['default_vehicle_id'])->exists();
            if (!$vehicleBelongsToDriver) {
                return response()->json(['message' => 'Invalid default vehicle selected'], 422);
            }
        }

        // Handle profile image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profile_images', 'public');
            $data['image'] = $path;
        }

        $userData = collect($data)->except(['driver_license', 'default_vehicle_id'])->toArray();
        $user->update($userData);

        if ($user->role === 'driver') {
            $driver = $user->driver;
            if ($driver) {
                $updateData = [];

                // Update default_vehicle_id if provided
                if (isset($data['default_vehicle_id'])) {
                    $updateData['default_vehicle_id'] = $data['default_vehicle_id'];
                }

                // Handle driver license file upload if not verified
                if (!$driver->is_verified && $request->hasFile('driver_license')) {
                    $licensePath = $request->file('driver_license')->store('driver_licenses', 'public');
                    $updateData['driver_license'] = $licensePath;
                }

                if (!empty($updateData)) {
                    $driver->update($updateData);
                }
            }
        }

        if ($user->role === 'driver') {
            $user->load('driver.vehicles');
        } elseif ($user->role === 'passenger') {
            $user->load('passenger');
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function changePass(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully']);
    }

    public function updateLocation(Request $request)
    {
        $data =  $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = $request->user();

        $locationService = new LocationService();
        $cityName = $locationService->getCityNameFromCoordinates($data['latitude'], $data['longitude']);

        $city = Location::where('type', 'city')
            ->whereRaw('LOWER(name) = ?', [strtolower($cityName)])
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
}
