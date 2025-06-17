<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\{RideTemplateGroup, RideTemplate, LocationGroup, LocationGroupLocationRel};

class RideTemplateGroupController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        return response()->json([
            'groups' => $request->user()->driver?->rideTemplateGroups ?? [],
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ride_templates' => 'required|array|min:1',
            'ride_templates.*.vehicle_id' => 'required|exists:vehicles,id',
            'ride_templates.*.scheduled_time' => 'required|date_format:H:i',
            'ride_templates.*.recurring_days' => 'nullable|array',
            'ride_templates.*.type' => 'required|in:to_institution,from_institution',
            'locations' => 'required|array|min:1',
            'locations.*.id' => 'required|exists:locations,id',
            'locations.*.type' => 'required|in:passenger,institution',
        ]);

        $driver = $request->user()->driver;

        DB::transaction(function () use ($request, $driver) {
            $locationGroup = LocationGroup::create();

            foreach ($request->locations as $loc) {
                LocationGroupLocationRel::create([
                    'location_group_id' => $locationGroup->id,
                    'location_id' => $loc['id'],
                    'location_type' => $loc['type'],
                ]);
            }

            $templateGroup = RideTemplateGroup::create([
                'name' => $request->name,
                'driver_id' => $driver->id,
                'location_group_id' => $locationGroup->id,
                'is_active' => true,
            ]);

            foreach ($request->ride_templates as $template) {
                RideTemplate::create([
                    'vehicle_id' => $template['vehicle_id'],
                    'ride_template_group_id' => $templateGroup->id,
                    'scheduled_time' => $template['scheduled_time'],
                    'recurring_days' => $template['recurring_days'] ?? null,
                    'type' => $template['type'],
                ]);
            }
        });

        return response()->json(['message' => 'Ride template group created successfully']);
    }

    public function show(Request $request, RideTemplateGroup $rideTemplateGroup)
    {
        $this->authorize('rud', $rideTemplateGroup);

        return response()->json([
            'ride_template_group' => $rideTemplateGroup->fresh()->load([
                'driver',
                'rideTemplates.vehicle',
                'locationGroup.locations'
            ])
        ]);
    }

    public function update(Request $request, RideTemplateGroup $rideTemplateGroup)
    {
        $this->authorize('rud', $rideTemplateGroup);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
            'ride_templates' => 'sometimes|array|min:1',
            'ride_templates.*.id' => 'required|exists:ride_templates,id',
            'ride_templates.*.vehicle_id' => 'sometimes|exists:vehicles,id',
            'ride_templates.*.scheduled_time' => 'sometimes|date_format:H:i',
            'ride_templates.*.recurring_days' => 'nullable|array',
            'ride_templates.*.type' => 'sometimes|in:to_institution,from_institution',
            'locations' => 'sometimes|array|min:1',
            'locations.*.id' => 'required|exists:locations,id',
            'locations.*.type' => 'required|in:passenger,institution',
        ]);

        DB::transaction(function () use ($request, $rideTemplateGroup) {
            // Update group name and status
            $rideTemplateGroup->update([
                'name' => $request->name ?? $rideTemplateGroup->name,
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $rideTemplateGroup->is_active,
            ]);

            // Update locations if present
            if ($request->filled('locations')) {
                $locationGroup = $rideTemplateGroup->locationGroup;

                // Remove old relations
                $locationGroup->locationGroupLocationRels()->delete();

                // Add new ones
                foreach ($request->locations as $loc) {
                    LocationGroupLocationRel::create([
                        'location_group_id' => $locationGroup->id,
                        'location_id' => $loc['id'],
                        'location_type' => $loc['type'],
                    ]);
                }
            }

            // Update ride templates
            if ($request->filled('ride_templates')) {
                $submittedIds = collect($request->ride_templates)->pluck('id')->toArray();

                // Delete ride templates that are no longer present
                $rideTemplateGroup->rideTemplates()
                    ->whereNotIn('id', $submittedIds)
                    ->delete();

                // Update existing ride templates
                foreach ($request->ride_templates as $template) {
                    $rideTemplate = $rideTemplateGroup->rideTemplates()->find($template['id']);

                    if ($rideTemplate) {
                        $rideTemplate->update([
                            'vehicle_id' => $template['vehicle_id'] ?? $rideTemplate->vehicle_id,
                            'scheduled_time' => $template['scheduled_time'] ?? $rideTemplate->scheduled_time,
                            'recurring_days' => $template['recurring_days'] ?? $rideTemplate->recurring_days,
                            'type' => $template['type'] ?? $rideTemplate->type,
                        ]);
                    }
                }
            }
        });

        return response()->json([
            'message' => 'Ride template group updated successfully',
            'ride_template_group' => $rideTemplateGroup->fresh()->load([
                'driver',
                'rideTemplates.vehicle',
                'locationGroup.locations',
            ])
        ]);
    }

    public function destroy(RideTemplateGroup $rideTemplateGroup)
    {
        $this->authorize('rud', $rideTemplateGroup);

        DB::transaction(function () use ($rideTemplateGroup) {
            // Delete all related ride templates
            $rideTemplateGroup->rideTemplates()->delete();

            // Delete related location group relations
            $rideTemplateGroup->locationGroup->locationGroupLocationRels()->delete();

            // Delete the location group itself
            $rideTemplateGroup->locationGroup()->delete();

            // Finally, delete the ride template group
            $rideTemplateGroup->delete();
        });

        return response()->json(['message' => 'Ride template group deleted successfully']);
    }
}
