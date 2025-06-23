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

        $passengerLocations = Location::where('type', 'city')->inRandomOrder()->limit(40)->get();
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

        $passengers = User::where('role', 'passenger')->whereIn('city_id', $passengerLocations->pluck('id'))->with(['passenger'])->inRandomOrder()->limit(30)->get();
        $institution = $instLocations->first();
        $vehicle = $driver->vehicles()->first();

        $hrs = [8, 9, 10];
        $x = 5;
        foreach ($hrs as $hr) {
            $ride = Ride::create([
                'vehicle_id' => $vehicle->id,
                'ride_group_id' => $rideGroup->id,
                'scheduled_time' => Carbon::create(2025, 7, 1, $hr, 0, 0),
                'type' => 'to_institution',
                'booked_seats' => 5,
                'available_seats' => 5,
                'status' => 'pending'
            ]);

            for ($i = $x - 5; $i < $x; $i++) {
                $passenger = $passengers[$i];
                $rideRequest = RideRequest::create([
                    'passenger_id' => $passenger->passenger->id,
                    'to_inst_ride_id' => $ride->id,
                    'passenger_latitude' => $passenger->latitude,
                    'passenger_longitude' => $passenger->longitude,
                    'institution_location_id' => $institution->id,
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
                        'dropoff_location_id' => $institution->id,
                        'dropoff_latitude' => $institution->latitude,
                        'dropoff_longitude' => $institution->longitude,
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
                    'status' => 'active'
                ]);
            }
            $x = $x + 5;
        }

        $passengers = User::where('role', 'passenger')->whereIn('city_id', $passengerLocations->pluck('id'))->with(['passenger'])->inRandomOrder()->limit(30)->get();

        $hrs = [13, 14, 15];
        $x = 4;
        foreach ($hrs as $hr) {
            $ride = Ride::create([
                'vehicle_id' => $vehicle->id,
                'ride_group_id' => $rideGroup->id,
                'scheduled_time' => Carbon::create(2025, 7, 1, $hr, 0, 0),
                'type' => 'from_institution',
                'booked_seats' => 5,
                'available_seats' => 5,
                'status' => 'pending'
            ]);


            for ($i = $x - 4; $i < $x; $i++) {
                $passenger = $passengers[$i];
                $rideRequest = RideRequest::create([
                    'passenger_id' => $passenger->passenger->id,
                    'to_inst_ride_id' => $ride->id,
                    'passenger_latitude' => $passenger->latitude,
                    'passenger_longitude' => $passenger->longitude,
                    'institution_location_id' => $institution->id,
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
                        'dropoff_location_id' => $institution->id,
                        'dropoff_latitude' => $institution->latitude,
                        'dropoff_longitude' => $institution->longitude,
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
                    'status' => 'active'
                ]);
            }
            $x = $x + 4;
        }
    }
}
