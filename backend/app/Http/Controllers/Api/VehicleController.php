<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
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
        ]);

        $vehicle = $request->user()->vehicles()->create($data);

        return response()->json([
            'message' => 'Vehicle added successfully',
            'vehicle' => $vehicle,
        ]);
    }

    public function update(Request $request, $id)
    {
        $vehicle = $request->user()->vehicles()->findOrFail($id);

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

    public function destroy(Request $request, $id)
    {
        $vehicle = $request->user()->vehicles()->findOrFail($id);
        $vehicle->delete();

        return response()->json(['message' => 'Vehicle deleted successfully']);
    }
}
