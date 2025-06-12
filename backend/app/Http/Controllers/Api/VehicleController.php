<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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
        $this->authorize('create');

        $data = $request->validate([
            'plate_number' => 'required|string|unique:vehicles',
            'brand' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:30',
        ]);

        $vehicle = $request->user()->vehicles()->create($data);

        return response()->json([
            'message' => 'Vehicle added successfully',
            'vehicle' => $vehicle,
        ]);
    }

    public function show(Request $request, Vehicle $vehicle)
    {
        $this->authorize('rud', $vehicle);

        return response()->json($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('rud', $vehicle);

        $data = $request->validate([
            'plate_number' => "required|string|unique:vehicles,plate_number,{$vehicle->id}",
            'brand' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:30',
        ]);

        $vehicle->update($data);

        return response()->json([
            'message' => 'Vehicle updated successfully',
            'vehicle' => $vehicle,
        ]);
    }

    public function destroy(Request $request, Vehicle $vehicle)
    {
        $this->authorize('rud', $vehicle);

        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted successfully']);
    }
}
