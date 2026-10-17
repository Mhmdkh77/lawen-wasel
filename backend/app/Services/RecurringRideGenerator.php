<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\RideGroup;
use App\Models\RideTemplate;
use App\Models\RideTemplateGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringRideGenerator
{
    public const BOOKING_WINDOW_DAYS = 7;

    public function generate(?RideTemplateGroup $onlyGroup = null): int
    {
        $query = RideTemplate::query()
            ->where('is_active', true)
            ->whereHas('rideTemplateGroup', fn ($groups) => $groups->where('is_active', true))
            ->with(['rideTemplateGroup.driver', 'rideTemplateGroup.locationGroup', 'vehicle']);

        if ($onlyGroup) {
            $query->where('ride_template_group_id', $onlyGroup->id);
        }

        $created = 0;
        $today = Carbon::today(config('app.timezone'));

        foreach ($query->get() as $template) {
            $group = $template->rideTemplateGroup;
            if (! $group?->driver || ! $group->locationGroup || ! $template->vehicle) {
                continue;
            }

            for ($offset = 0; $offset < self::BOOKING_WINDOW_DAYS; $offset++) {
                $date = $today->copy()->addDays($offset);
                if (! in_array(strtolower($date->englishDayOfWeek), $template->recurring_days ?? [], true)) {
                    continue;
                }

                $scheduled = $date->copy()->setTimeFromTimeString($template->scheduled_time);
                if ($scheduled->lte(now())) {
                    continue;
                }

                $created += DB::transaction(function () use ($template, $group, $scheduled) {
                    // Older generated rides have no template ID. Avoid duplicating those
                    // exact departures when upgrading an existing database.
                    $alreadyExists = Ride::query()
                        ->where('ride_template_id', $template->id)
                        ->where('scheduled_time', $scheduled)
                        ->exists();
                    $legacyExists = Ride::query()
                        ->whereNull('ride_template_id')
                        ->where('vehicle_id', $template->vehicle_id)
                        ->where('type', $template->type)
                        ->where('scheduled_time', $scheduled)
                        ->whereHas('rideGroup', fn ($rides) => $rides
                            ->where('driver_id', $group->driver_id)
                            ->where('location_group_id', $group->location_group_id))
                        ->exists();

                    if ($alreadyExists || $legacyExists) {
                        return 0;
                    }

                    $rideGroup = RideGroup::create([
                        'driver_id' => $group->driver_id,
                        'location_group_id' => $group->location_group_id,
                    ]);

                    Ride::create([
                        'ride_template_id' => $template->id,
                        'vehicle_id' => $template->vehicle_id,
                        'ride_group_id' => $rideGroup->id,
                        'scheduled_time' => $scheduled,
                        'type' => $template->type,
                        'available_seats' => $template->vehicle->capacity,
                    ]);

                    return 1;
                });
            }
        }

        return $created;
    }
}
