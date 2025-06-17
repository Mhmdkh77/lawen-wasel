<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Location;
use App\Models\Passenger;
use App\Models\Rating;
use App\Models\Ride;
use App\Models\User;
use App\Models\Vehicle;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(LocationSeeder::class); // create cities + institutions
        Location::factory(20)->create(); // create stations
        Admin::factory(1)->create();
        Passenger::factory(200)->create();
        Vehicle::factory(10)->create();

        // $this->call(RideSeeder::class);
        // Booking::factory(50)->create();
        // Rating::factory(20)->create();
    }
}
