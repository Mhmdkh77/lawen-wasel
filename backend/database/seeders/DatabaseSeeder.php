<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Location;
use App\Models\Passenger;
use App\Models\Rating;
use App\Models\Ride;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleImage;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
        for ($i = 0; $i < 10; $i++) {
            $vehicle = Vehicle::factory()->create();

            $files = Storage::disk('public')->files('vehicle_images');

            for ($j = 0; $j < 2; $j++) {
                $randomFile = fake()->randomElement($files);

                VehicleImage::create([
                    'vehicle_id' => $vehicle->id,
                    'path' => $randomFile,
                ]);
            }
        }



        $city = Location::cities()->inRandomOrder()->first();

        $user1 =  User::create([
            'name' => 'passenger',
            'email' => 'passenger@user.com',
            'email_verified_at' => now(),
            'phone' => '12345678',
            'password' => bcrypt('pass'),
            'role' => 'passenger',
            'gender' => 'male',
            'latitude' => $city->latitude + fake()->randomFloat(6, -0.002, 0.002),
            'longitude' => $city->longitude + fake()->randomFloat(6, -0.002, 0.002),
            'city_id' => $city->id,
        ]);

        Passenger::create(
            [
                'user_id' => $user1->id
            ]
        );


        $city = Location::cities()->inRandomOrder()->first();
        $user2 =  User::create([
            'name' => 'driver',
            'email' => 'driver@user.com',
            'email_verified_at' => now(),
            'phone' => '12345678',
            'password' => bcrypt('pass'),
            'role' => 'driver',
            'latitude' => $city->latitude + fake()->randomFloat(6, -0.002, 0.002),
            'longitude' => $city->longitude + fake()->randomFloat(6, -0.002, 0.002),
            'gender' => 'male',
            'city_id' => $city->id,
        ]);

        $driver = Driver::create([
            'user_id' => $user2->id,
            'is_verified' => true
        ]);

        Vehicle::factory(1)->create(['driver_id' => $driver->id, 'capacity' => 10]);

        $this->call(RideSeeder::class);

        // $this->call(RideSeeder::class);
        // Booking::factory(50)->create();
        // Rating::factory(20)->create();
    }
}
