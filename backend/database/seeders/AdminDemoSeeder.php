<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Booking;
use App\Models\BookingGroup;
use App\Models\Driver;
use App\Models\Location;
use App\Models\LocationGroup;
use App\Models\LocationGroupLocationRel;
use App\Models\Node;
use App\Models\Passenger;
use App\Models\Rating;
use App\Models\Ride;
use App\Models\RideGroup;
use App\Models\RideOffer;
use App\Models\RideRequest;
use App\Models\RideTemplate;
use App\Models\RideTemplateGroup;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Database\Seeder;

class AdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmins();

        Passenger::factory(200)->create();
        $passengers = Passenger::inRandomOrder()->limit(150)->get();

        $drivers = $this->seedDrivers();
        $vehicles = $this->seedVehicles($drivers);

        $driversWithVehicles = $drivers->filter(fn($driver) => $vehicles->where('driver_id', $driver->id)->isNotEmpty())->values();

        $rideGroups = $this->seedRideGroups($driversWithVehicles);
        $rides = $this->seedRides($rideGroups, $vehicles);

        $this->seedRideRequestsAndBookings($rides, $passengers);
        $this->seedRoundTripBookings($rides, $passengers);

        $this->seedRideTemplates($driversWithVehicles, $vehicles);
        $this->seedRatings();
    }

    private function seedAdmins(): void
    {
        Admin::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin'),
        ]);

        Admin::factory(2)->create();
    }

    private function createKnownUser(string $name, string $email, string $role): User
    {
        $city = Location::cities()->inRandomOrder()->first();

        return User::create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'phone' => '12345678',
            'password' => bcrypt('pass'),
            'role' => $role,
            'gender' => 'male',
            'latitude' => $city->latitude + fake()->randomFloat(6, -0.002, 0.002),
            'longitude' => $city->longitude + fake()->randomFloat(6, -0.002, 0.002),
            'city_id' => $city->id,
        ]);
    }

    private function seedDrivers()
    {
        $knownDriverUser = $this->createKnownUser('driver', 'driver@user.com', 'driver');
        $knownDriver = Driver::create(['user_id' => $knownDriverUser->id, 'is_verified' => true]);

        $knownPassengerUser = $this->createKnownUser('passenger', 'passenger@user.com', 'passenger');
        Passenger::create(['user_id' => $knownPassengerUser->id]);

        $drivers = collect([$knownDriver]);

        for ($i = 0; $i < 40; $i++) {
            $drivers->push(Driver::factory()->create());
        }

        // Force a guaranteed handful of unverified drivers rather than relying on
        // the factory's probabilistic default alone.
        $drivers->skip(1)->random(8)->each(fn($driver) => $driver->update(['is_verified' => false]));

        return $drivers;
    }

    private function seedVehicles($drivers)
    {
        $knownDriver = $drivers->first();
        $vehicles = collect([Vehicle::factory()->create(['driver_id' => $knownDriver->id, 'capacity' => 10])]);

        foreach ($drivers->skip(1) as $driver) {
            $vehicleCount = fake()->randomElement([1, 1, 1, 2]);

            for ($i = 0; $i < $vehicleCount; $i++) {
                $vehicles->push(Vehicle::factory()->create(['driver_id' => $driver->id]));
            }
        }

        foreach ($vehicles as $vehicle) {
            VehicleImage::factory(2)->create(['vehicle_id' => $vehicle->id]);
        }

        // Give every driver with at least one vehicle a default vehicle.
        foreach ($drivers as $driver) {
            $firstVehicle = $vehicles->firstWhere('driver_id', $driver->id);
            if ($firstVehicle) {
                $driver->update(['default_vehicle_id' => $firstVehicle->id]);
            }
        }

        return $vehicles;
    }

    private function seedRideGroups($driversWithVehicles)
    {
        $rideGroups = collect();
        $pool = $driversWithVehicles->random(min(15, $driversWithVehicles->count()));
        $institutions = Location::institutions()->get();

        foreach ($pool as $driver) {
            $locationGroup = LocationGroup::create();

            foreach (Location::cities()->inRandomOrder()->limit(5)->get() as $city) {
                LocationGroupLocationRel::create([
                    'location_group_id' => $locationGroup->id,
                    'location_id' => $city->id,
                    'location_type' => 'passenger',
                ]);
            }

            foreach ($institutions as $institution) {
                LocationGroupLocationRel::create([
                    'location_group_id' => $locationGroup->id,
                    'location_id' => $institution->id,
                    'location_type' => 'institution',
                ]);
            }

            $rideGroups->push(RideGroup::create([
                'driver_id' => $driver->id,
                'location_group_id' => $locationGroup->id,
            ]));
        }

        return $rideGroups;
    }

    private function seedRides($rideGroups, $vehicles)
    {
        $rides = collect();

        $statuses = collect()
            ->concat(array_fill(0, 25, 'pending'))
            ->concat(array_fill(0, 25, 'active'))
            ->concat(array_fill(0, 25, 'completed'))
            ->concat(array_fill(0, 25, 'canceled'))
            ->shuffle();

        foreach ($statuses as $status) {
            $rideGroup = $rideGroups->random();
            $vehicle = $vehicles->where('driver_id', $rideGroup->driver_id)->first();
            $type = fake()->randomElement(['to_institution', 'from_institution']);

            $scheduledTime = in_array($status, ['completed', 'canceled'])
                ? fake()->dateTimeBetween('-3 weeks', '-1 day')
                : fake()->dateTimeBetween('now', '+2 weeks');

            $ride = Ride::create([
                'vehicle_id' => $vehicle->id,
                'ride_group_id' => $rideGroup->id,
                'scheduled_time' => $scheduledTime,
                'type' => $type,
                'booked_seats' => 0,
                'available_seats' => $vehicle->capacity,
                'status' => $status,
                'start_time' => in_array($status, ['active', 'completed']) ? $scheduledTime : null,
                'finish_time' => $status === 'completed' ? $scheduledTime : null,
            ]);

            $rides->push($ride);
        }

        return $rides;
    }

    private function createAcceptedBooking(Ride $ride, $passenger, string $type = 'one_way', ?BookingGroup $bookingGroup = null): Booking
    {
        $institution = Location::institutions()->inRandomOrder()->first();

        $rideRequest = RideRequest::factory()->accepted()->create([
            'passenger_id' => $passenger->id,
            'to_inst_ride_id' => $ride->type === 'to_institution' ? $ride->id : null,
            'from_inst_ride_id' => $ride->type === 'from_institution' ? $ride->id : null,
            'institution_location_id' => $institution->id,
            'type' => $type,
        ]);

        $offer = RideOffer::factory()->accepted()->create([
            'ride_request_id' => $rideRequest->id,
            'driver_id' => $ride->vehicle->driver_id,
        ]);

        $node = Node::create([
            'ride_id' => $ride->id,
            'pickup_location_id' => null,
            'pickup_latitude' => $rideRequest->passenger_latitude,
            'pickup_longitude' => $rideRequest->passenger_longitude,
            'dropoff_location_id' => $institution->id,
            'dropoff_latitude' => $institution->latitude,
            'dropoff_longitude' => $institution->longitude,
            'status' => $ride->status === 'completed' ? 'completed' : 'pending',
        ]);

        $status = 'active';
        $roll = fake()->numberBetween(1, 100);
        if ($roll <= 15) {
            $status = 'passenger_canceled';
        } elseif ($ride->status === 'canceled' && $roll <= 30) {
            $status = 'ride_canceled';
        }

        $booking = Booking::create([
            'passenger_id' => $passenger->id,
            'ride_id' => $ride->id,
            'booking_group_id' => $bookingGroup?->id ?? BookingGroup::create(['passenger_id' => $passenger->id])->id,
            'ride_request_id' => $rideRequest->id,
            'node_id' => $node->id,
            'nb_seats' => 1,
            'price' => $offer->offered_price,
            'status' => $status,
        ]);

        if ($status === 'active') {
            $ride->increment('booked_seats');
            $ride->decrement('available_seats');
        }

        return $booking;
    }

    private function seedRideRequestsAndBookings($rides, $passengers): void
    {
        $nonBookingStatuses = ['pending', 'driver_offered', 'rejected', 'canceled', 'expired'];
        $offerStatusFor = [
            'driver_offered' => 'pending',
            'rejected' => 'rejected',
            'canceled' => 'expired',
        ];

        foreach ($rides as $ride) {
            $extraCount = fake()->numberBetween(0, 1);
            $usedPassengerIds = [];

            for ($i = 0; $i < $extraCount; $i++) {
                $status = fake()->randomElement($nonBookingStatuses);
                $passenger = $passengers->random();
                $institution = Location::institutions()->inRandomOrder()->first();

                $rideRequest = RideRequest::factory()->create([
                    'passenger_id' => $passenger->id,
                    'to_inst_ride_id' => $ride->type === 'to_institution' ? $ride->id : null,
                    'from_inst_ride_id' => $ride->type === 'from_institution' ? $ride->id : null,
                    'institution_location_id' => $institution->id,
                    'status' => $status,
                ]);

                if (isset($offerStatusFor[$status])) {
                    RideOffer::factory()->create([
                        'ride_request_id' => $rideRequest->id,
                        'driver_id' => $ride->vehicle->driver_id,
                        'status' => $offerStatusFor[$status],
                    ]);
                }
            }

            $acceptedCount = min(fake()->numberBetween(0, 1), $ride->available_seats);

            for ($i = 0; $i < $acceptedCount; $i++) {
                $passenger = $passengers->whereNotIn('id', $usedPassengerIds)->random();
                $usedPassengerIds[] = $passenger->id;
                $this->createAcceptedBooking($ride, $passenger);
            }
        }
    }

    private function seedRoundTripBookings($rides, $passengers): void
    {
        $toRides = $rides->where('type', 'to_institution')->where('available_seats', '>', 0)->values();
        $fromRides = $rides->where('type', 'from_institution')->where('available_seats', '>', 0)->values();

        $pairs = min(15, $toRides->count(), $fromRides->count());

        for ($i = 0; $i < $pairs; $i++) {
            $toRide = $toRides->random();
            $fromRide = $fromRides->random();
            $passenger = $passengers->random();

            if (Booking::where('ride_id', $toRide->id)->where('passenger_id', $passenger->id)->exists()) {
                continue;
            }
            if (Booking::where('ride_id', $fromRide->id)->where('passenger_id', $passenger->id)->exists()) {
                continue;
            }

            $bookingGroup = BookingGroup::create(['passenger_id' => $passenger->id]);
            $this->createAcceptedBooking($toRide, $passenger, 'round_trip', $bookingGroup);
            $this->createAcceptedBooking($fromRide, $passenger, 'round_trip', $bookingGroup);
        }
    }

    private function seedRideTemplates($driversWithVehicles, $vehicles): void
    {
        $pool = $driversWithVehicles->random(min(15, $driversWithVehicles->count()));

        foreach ($pool as $driver) {
            $driverVehicles = $vehicles->where('driver_id', $driver->id)->values();

            $locationGroup = LocationGroup::create();

            foreach (Location::cities()->inRandomOrder()->limit(3)->get() as $city) {
                LocationGroupLocationRel::create([
                    'location_group_id' => $locationGroup->id,
                    'location_id' => $city->id,
                    'location_type' => 'passenger',
                ]);
            }

            foreach (Location::institutions()->get() as $institution) {
                LocationGroupLocationRel::create([
                    'location_group_id' => $locationGroup->id,
                    'location_id' => $institution->id,
                    'location_type' => 'institution',
                ]);
            }

            $group = RideTemplateGroup::create([
                'name' => fake()->randomElement(['Morning Route', 'Evening Route', 'Weekday Commute', 'Campus Shuttle']),
                'driver_id' => $driver->id,
                'location_group_id' => $locationGroup->id,
                'is_active' => fake()->boolean(80),
            ]);

            $templateCount = fake()->numberBetween(2, 4);

            for ($i = 0; $i < $templateCount; $i++) {
                RideTemplate::factory()->create([
                    'ride_template_group_id' => $group->id,
                    'vehicle_id' => $driverVehicles->random()->id,
                ]);
            }
        }
    }

    private function seedRatings(): void
    {
        for ($score = 1; $score <= 5; $score++) {
            try {
                Rating::factory()->create(['rating' => $score]);
            } catch (\Throwable $e) {
                // Not enough eligible completed-ride/passenger pairs left for this score; skip.
            }
        }

        $created = 0;
        $attempts = 0;

        while ($created < 55 && $attempts < 300) {
            $attempts++;

            try {
                Rating::factory()->create();
                $created++;
            } catch (\Throwable $e) {
                // Random pick had no eligible unrated passenger; try again.
            }
        }
    }
}
