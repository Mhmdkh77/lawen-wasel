<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        return view('locations.index');
    }

    public function create()
    {
        $cities = Location::where('type', 'city')->get();
        return view('locations.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:city,station,institution',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'city_id' => 'nullable|exists:locations,id',
        ]);

        if ($request->type === 'city' && $request->city_id !== null) {
            return back()->withErrors(['city_id' => 'Cities cannot have parent cities.']);
        }


        Location::create($request->only('name', 'latitude', 'longitude', 'type', 'city_id'));

        return redirect()->route('admin.locations.index')->with('success', 'Location added.');
    }

    public function edit(Location $location)
    {
        $cities = Location::where('type', 'city')->get();
        return view('locations.edit', compact('location', 'cities'));
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:city,station,institution',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'city_id' => 'nullable|exists:locations,id',
        ]);

        if ($request->type === 'city' && $request->city_id !== null) {
            return back()->withErrors(['city_id' => 'Cities cannot have parent cities.']);
        }

        Location::create($request->only('name', 'latitude', 'longitude', 'type', 'city_id'));

        return redirect()->route('admin.locations.index')->with('success', 'Location updated.');
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('admin.locations.index')->with('success', 'Location deleted successfully.');
    }
}
