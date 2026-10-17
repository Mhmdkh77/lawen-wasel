<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Services\LocationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\DriverLicenseStorage;

class UserController extends Controller
{
    public function getUser(Request $request)
    {
        $user = $request->user()->fresh();

        if ($user->role === 'driver') {
            $user->load('driver.vehicles');

            $driverLicenseUrl = $user->driver && $user->driver->driver_license
                ? route('api.user.driver-license')
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

    public function driverLicense(Request $request, DriverLicenseStorage $storage)
    {
        return $storage->response($request->user()->driver?->driver_license);
    }

    public function updateUser(Request $request)
    {
        if ($request->getRealMethod() === 'PUT'
            && str_starts_with(strtolower($request->header('Content-Type', '')), 'multipart/form-data')) {
            return response()->json([
                'message' => 'Multipart PUT files are not parsed by PHP. Send a multipart POST to /api/user with _method=PUT.',
            ], 415);
        }

        $user = $request->user()->fresh();

        if ($request->hasFile('driver_license')
            && ($user->role !== 'driver' || $user->driver?->is_verified)) {
            return response()->json(['message' => 'This driver license cannot be updated.'], 403);
        }

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

        if (array_key_exists('default_vehicle_id', $data) && $data['default_vehicle_id'] !== null) {
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
                    $oldLicensePath = $driver->driver_license;
                    $licensePath = $request->file('driver_license')->store('driver_licenses', 'local');
                    $updateData['driver_license'] = $licensePath;
                }

                if (!empty($updateData)) {
                    $driver->update($updateData);

                    if (isset($licensePath, $oldLicensePath)
                        && $oldLicensePath !== $licensePath
                        && ! DriverLicenseStorage::isDemoFile($oldLicensePath)
                        && ! Driver::where('driver_license', $oldLicensePath)->exists()) {
                        Storage::disk('local')->delete($oldLicensePath);
                    }
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
            'city_id' => $city->id,
        ]);

        return response()->json(['message' => 'Location updated']);
    }
}
