<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateRidesFromTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-rides-from-templates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //     $today = now()->dayOfWeek; // 0-6

        //     $templates = RideTemplate::where('is_active', true)->get();

        //     foreach ($templates as $template) {
        //         if (!in_array($today, $template->recurring_days)) {
        //             continue;
        //         }

        //         // Avoid duplicate generation for same week
        //         $last = $template->last_generated_at;
        //         if ($last && \Carbon\Carbon::parse($last)->greaterThanOrEqualTo(now()->startOfWeek())) {
        //             continue;
        //         }

        //         // Create the ride from template
        //         $ride = Ride::create([
        //             'driver_id' => $template->driver_id,
        //             'departure_time' => now()->setTimeFromTimeString($template->departure_time),
        //             'template_id' => $template->id,
        //             // Add more fields if needed
        //         ]);

        //         // Optionally create ride nodes
        //         foreach ($template->nodes as $templateNode) {
        //             $ride->nodes()->create([
        //                 'type' => $templateNode->type,
        //                 'latitude' => $templateNode->latitude,
        //                 'longitude' => $templateNode->longitude,
        //                 'destination_id' => $templateNode->destination_id,
        //             ]);
        //         }

        //         $template->update(['last_generated_at' => now()]);
        //     }

        //     $this->info("Generated rides from templates.");
    }
}
