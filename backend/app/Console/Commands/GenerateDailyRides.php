<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RecurringRideGenerator;

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
    protected $description = 'Maintain the next seven days of rides from active templates';

    /**
     * Execute the console command.
     */
    public function handle(RecurringRideGenerator $generator): int
    {
        $count = $generator->generate();
        $this->info("Created {$count} upcoming rides.");

        return self::SUCCESS;
    }
}
