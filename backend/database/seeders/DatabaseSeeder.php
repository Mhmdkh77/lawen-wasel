<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\PaymentMethod;
use App\Models\Payments;
use App\Models\Rating;
use App\Models\Ride;
use App\Models\Route;
use App\Models\Station;
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
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        PaymentMethod::create(
            [
                'name' => 'Cash',
                'description' => 'Cash Payment'
            ]
        );

        Admin::factory(1)->create();
        User::factory(500)->create();
        User::factory(20)->driver()->create();
        Station::factory(10)->create();
        Vehicle::factory(40)->create();
        Ride::factory(20)->create();
        Booking::factory(100)->create();
        // Payments::factory(20)->create();
        // Rating::factory(20)->create();
    }
}
