<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Node;
use App\Models\Ride;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rides =  Ride::factory(15)->create();

        foreach ($rides as $ride) {

            // attach random institution
            $institution = Location::institutions()->inRandomOrder()->first();
            $ride->locations()->attach($institution->id, ['type' => 'dropoff']);

            Node::create([
                'ride_id' => $ride->id,
                'location_id' => $institution->id,
                'latitude' => $institution->latitude,
                'longitude' => $institution->longitude,
                'type' => 'dropoff'
            ]);

            // attach random cities
            $cities = Location::cities()->inRandomOrder()->take(5)->get();
            $cityAttachData = [];

            foreach ($cities as $city) {
                $cityAttachData[$city->id] = ['type' => 'pickup'];
            }

            $ride->locations()->attach($cityAttachData);

            // attach random station
            $cityIds = $cities->pluck('id')->toArray();

            $station = Location::stations()
                ->whereNotIn('city_id', $cityIds)
                ->inRandomOrder()
                ->first();
            $ride->locations()->attach($station->id, ['type' => 'pickup']);
        }
    }
}
