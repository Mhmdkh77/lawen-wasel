<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $vehicles = $request->user()->vehicles;
        return response()->json($vehicles);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plate_number' => 'required|string|unique:vehicles',
            'brand' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:30',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        $vehicle = $user->vehicles()->create($data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('vehicle_images', 'public');
                $vehicle->images()->create(['path' => $path]);
            }
        }

        if ($user->role === 'driver' && $user->vehicles()->count() === 1) {
            if ($user->driver) {
                $user->driver->update(['default_vehicle_id' => $vehicle->id]);
            }
        }

        return response()->json([
            'message' => 'Vehicle added successfully',
            'vehicle' => $vehicle->load('images'),
        ]);
    }

    public function show(Request $request, Vehicle $vehicle)
    {
        $this->authorize('rud', $vehicle);

        return response()->json($vehicle->load('images'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('rud', $vehicle);

        $data = $request->validate([
            'plate_number' => "required|string|unique:vehicles,plate_number,{$vehicle->id}",
            'brand' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:30',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'delete_image_ids' => 'array',
            'delete_image_ids.*' => 'integer|exists:vehicle_images,id',
        ]);

        $vehicle->update($data);

        if (isset($data['delete_image_ids'])) {
            foreach ($data['delete_image_ids'] as $imageId) {
                $image = $vehicle->images()->find($imageId);
                if ($image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }
            }
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('vehicle_images', 'public');
                $vehicle->images()->create(['path' => $path]);
            }
        }

        return response()->json([
            'message' => 'Vehicle updated successfully',
            'vehicle' => $vehicle->fresh()->load('images'),
        ]);
    }

    public function destroy(Request $request, Vehicle $vehicle)
    {
        $this->authorize('rud', $vehicle);

        foreach ($vehicle->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted successfully']);
    }
}
