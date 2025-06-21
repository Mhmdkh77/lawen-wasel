<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RideTemplate;
use App\Models\RideGroup;
use App\Models\Ride;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GenerateDailyRides extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-daily-rides';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily rides from active ride templates based on recurring_days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = strtolower(Carbon::now()->format('l'));
        $date = now()->toDateString();

        RideTemplate::where('is_active', true)
            ->whereJsonContains('recurring_days', $today)
            ->with(['rideTemplateGroup.driver', 'rideTemplateGroup.locationGroup'])
            ->each(function ($template) use ($date) {
                $group = $template->rideTemplateGroup;
                $driver = $group->driver ?? null;
                $locationGroup = $group->locationGroup ?? null;

                if (! $driver || ! $locationGroup) {
                    $this->warn("Skipping template {$template->id} — missing driver or location group.");
                    return;
                }

                $alreadyExists = Ride::where('vehicle_id', $template->vehicle_id)
                    ->where('type', $template->type)
                    ->whereDate('scheduled_time', $date)
                    ->exists();

                if ($alreadyExists) {
                    $this->line("Ride for template {$template->id} already exists today.");
                    return;
                }

                try {
                    DB::transaction(function () use ($template, $driver, $locationGroup) {
                        $rideGroup = RideGroup::create([
                            'driver_id' => $driver->id,
                            'location_group_id' => $locationGroup->id,
                        ]);

                        Ride::create([
                            'vehicle_id' => $template->vehicle_id,
                            'ride_group_id' => $rideGroup->id,
                            'scheduled_time' => $template->scheduled_time,
                            'type' => $template->type,
                        ]);
                    });

                    $this->info("Ride created from template {$template->id}");
                } catch (\Exception $e) {
                    $this->error("Error with template {$template->id}: " . $e->getMessage());
                }
            });
    }
}
