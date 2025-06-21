<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingGroup;
use App\Models\Location;
use App\Models\LocationGroup;
use App\Models\LocationGroupLocationRel;
use App\Models\Node;
use App\Models\Ride;
use App\Models\RideGroup;
use App\Models\RideOffer;
use App\Models\RideRequest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $driver = User::where("name", "driver")->with(['driver'])->first()->driver;

        $locationGroup =  LocationGroup::create();

        $passengerLocations = Location::where('type', 'city')->inRandomOrder()->limit(20)->get();
        $instLocations = Location::where('type', 'institution')->get();

        foreach ($passengerLocations as $location) {
            LocationGroupLocationRel::create([
                'location_group_id' => $locationGroup->id,
                'location_id' => $location->id,
                'location_type' => 'passenger'
            ]);
        }

        foreach ($instLocations as $location) {
            LocationGroupLocationRel::create([
                'location_group_id' => $locationGroup->id,
                'location_id' => $location->id,
                'location_type' => 'institution'
            ]);
        }

        $rideGroup = RideGroup::create(
            [
                'driver_id' => $driver->id,
                'location_group_id' => $locationGroup->id,
            ]
        );

        $passengers = User::where('role', 'passenger')->whereIn('city_id', $passengerLocations)->with(['passenger'])->inRandomOrder()->limit(30)->get();

        $hrs = [8, 9, 10];
        foreach ($hrs as $hr) {
            $ride = Ride::create([
                'vehicle_id' => $driver->vehicles()->first()->id,
                'ride_group_id' => $rideGroup->id,
                'scheduled_time' => Carbon::create(2025, 7, 1, $hr, 0, 0),
                'type' => 'to_institution',
                'booked_seats' => 0,
                'available_seats' => $driver->vehicles()->first()->capacity,
                'status' => 'pending'
            ]);

            for ($i = 0; $i < 5; $i++) {
                $passenger = $passengers[$i];
                $rideRequest = RideRequest::create([
                    'passenger_id' => $passenger->passenger->id,
                    'to_inst_ride_id' => $ride->id,
                    'passenger_latitude' => $passenger->latitude,
                    'passenger_longitude' => $passenger->longitude,
                    'institution_location_id' => $instLocations->first->id,
                    'nb_seats_requested' => 1,
                    'type' => 'one_way',
                    'status' => 'accepted'
                ]);

                $rideOffer = RideOffer::create([
                    'ride_request_id' => $rideRequest->id,
                    'driver_id' => $driver->id,
                    'offered_price' => 100,
                    'suggested_pickup_latitude' => $passenger->latitude,
                    'suggested_pickup_longitude' => $passenger->longitude,
                    'pickup_time' => Carbon::create(2025, 7, 1, $hr - 1, 30, 0),
                    'status' => 'accepted'
                ]);

                $node = Node::create(
                    [
                        'ride_id' => $ride->id,
                        'pickup_latitude' => $passenger->latitude,
                        'pickup_longitude' => $passenger->longitude,
                        'dropoff_location_id' => $instLocations->first->id,
                        'dropoff_latitude' => $instLocations->first->laitude,
                        'dropoff_longitude' => $instLocations->first->longitude,
                        'status' => 'pending',
                    ]
                );

                $bookingGroup =  BookingGroup::create([
                    'passenger_id' => $passenger->passenger->id,
                ]);

                Booking::create([
                    'passenger_id' => $passenger->passenger->id,
                    'ride_id' => $ride->id,
                    'booking_group_id' => $bookingGroup->id,
                    'ride_request_id' => $rideRequest->id,
                    'node_id' => $node->id,
                    'nb_seats' => 1,
                    'price' => 100,
                ]);
            }
        }

        $hrs = [13, 14, 15];
        foreach ($hrs as $hr) {
            Ride::create([
                'vehicle_id' => $driver->vehicles()->first()->id,
                'ride_group_id' => $rideGroup->id,
                'scheduled_time' => Carbon::create(2025, 7, 1, $hr, 0, 0),
                'type' => 'from_institution',
                'booked_seats' => 0,
                'available_seats' => $driver->vehicles()->first()->capacity,
                'status' => 'pending'
            ]);
        }
    }
}
