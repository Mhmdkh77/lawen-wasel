<?php

namespace Database\Seeders;

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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $locations = $this->locations();
            $drivers = $this->drivers($locations);
            $passengers = $this->passengers($locations);
            $rides = $this->rides($drivers, $locations);

            $this->bookingsAndRequests($rides, $drivers, $passengers, $locations);
            $this->templates($drivers);
        });
    }

    private function locations(): array
    {
        $definitions = [
            'nabatieh' => ['Nabatiye el Tahta', 'city', 33.37696, 35.48455],
            'kfar_roummane' => ['Kfar Roummane', 'city', 33.38864, 35.50153],
            'habbouch' => ['Habbouch', 'city', 33.40723, 35.48333],
            'maifadoun' => ['Maifadoun', 'city', 33.34740, 35.47538],
            'kfar_tebnit' => ['Kfar Tebnit', 'city', 33.35371, 35.51255],
            'zebdine' => ['Zebdine El Nabatieh', 'city', 33.37368, 35.46282],
            'kfour' => ['Kfour El Nabatieh', 'city', 33.40029, 35.45294],
            'jibchit' => ['Jibchit', 'city', 33.36340, 35.43193],
            'harouf' => ['Harouf', 'city', 33.37488, 35.43831],
            'toul' => ['Toul', 'city', 33.38783, 35.44768],
            'campus' => ['Lebanese University', 'institution', 33.37749271, 35.49657675],
            'liu' => ['LIU', 'institution', 33.37702701, 35.49739395],
            'station' => ['Showcase Nabatieh Central Stop', 'station', 33.37715, 35.48465],
        ];

        $locations = [];
        foreach ($definitions as $key => [$name, $type, $latitude, $longitude]) {
            $locations[$key] = Location::firstOrCreate(
                ['name' => $name, 'type' => $type],
                ['latitude' => $latitude, 'longitude' => $longitude],
            );
        }

        return $locations;
    }

    private function drivers(array $locations): array
    {
        $definitions = [
            'mira' => ['Mira Haddad', 'showcase.mira@example.test', 'nabatieh', true, 'SHOW-101', 'Mercedes Sprinter', 'White', 6, 'bus01.jpg', 'pfp01.jpg'],
            'rami' => ['Rami Mansour', 'showcase.rami@example.test', 'habbouch', true, 'SHOW-202', 'Toyota Corolla', 'Silver', 4, 'bus02.jpg', 'pfp02.jpg'],
            'leila' => ['Leila Saad', 'showcase.leila@example.test', 'kfar_roummane', true, 'SHOW-303', 'Hyundai Elantra', 'Blue', 4, 'bus03.jpg', 'pfp03.jpg'],
            'karim' => ['Karim Nasser', 'showcase.karim@example.test', 'maifadoun', false, 'SHOW-404', 'Kia Carnival', 'Black', 6, 'bus04.jpg', 'pfp04.jpg'],
        ];

        $drivers = [];
        foreach ($definitions as $key => [$name, $email, $cityKey, $verified, $plate, $brand, $color, $capacity, $photo, $profile]) {
            $city = $locations[$cityKey];
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'phone' => '7000' . str_pad((string) (count($drivers) + 1), 4, '0', STR_PAD_LEFT),
                'password' => Hash::make('pass'),
                'role' => 'driver',
                'gender' => $key === 'mira' || $key === 'leila' ? 'female' : 'male',
                'email_verified_at' => now(),
                'city_id' => $city->id,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
                'image' => 'profile_images/' . $profile,
            ]);
            $driver = Driver::firstOrCreate(['user_id' => $user->id], ['is_verified' => $verified]);
            $vehicle = Vehicle::firstOrCreate(['plate_number' => $plate], [
                'driver_id' => $driver->id,
                'brand' => $brand,
                'color' => $color,
                'capacity' => $capacity,
            ]);
            VehicleImage::firstOrCreate(['vehicle_id' => $vehicle->id, 'path' => 'vehicle_images/' . $photo]);
            $driver->update(['default_vehicle_id' => $vehicle->id]);
            $drivers[$key] = ['driver' => $driver, 'user' => $user, 'vehicle' => $vehicle];
        }

        $spare = Vehicle::firstOrCreate(['plate_number' => 'SHOW-102'], [
            'driver_id' => $drivers['mira']['driver']->id,
            'brand' => 'Toyota Hiace',
            'color' => 'Gray',
            'capacity' => 8,
        ]);
        VehicleImage::firstOrCreate(['vehicle_id' => $spare->id, 'path' => 'vehicle_images/bus05.jpg']);

        return $drivers;
    }

    private function passengers(array $locations): array
    {
        $definitions = [
            'nadia' => ['Nadia Daher', 'kfar_roummane'],
            'youssef' => ['Youssef Hamdan', 'habbouch'],
            'mariam' => ['Mariam Fadel', 'maifadoun'],
            'bilal' => ['Bilal Saleh', 'kfar_tebnit'],
            'maya' => ['Maya Khalil', 'zebdine'],
            'omar' => ['Omar Abbas', 'kfour'],
            'lina' => ['Lina Haidar', 'jibchit'],
            'hadi' => ['Hadi Moussa', 'harouf'],
            'sara' => ['Sara Awad', 'toul'],
            'dana' => ['Dana Farhat', 'nabatieh'],
            'firas' => ['Firas Younes', 'kfar_roummane'],
            'salma' => ['Salma Mahdi', 'habbouch'],
        ];

        $passengers = [];
        foreach ($definitions as $key => [$name, $cityKey]) {
            $city = $locations[$cityKey];
            $user = User::firstOrCreate(['email' => 'showcase.' . $key . '@example.test'], [
                'name' => $name,
                'phone' => '7100' . str_pad((string) (count($passengers) + 1), 4, '0', STR_PAD_LEFT),
                'password' => Hash::make('pass'),
                'role' => 'passenger',
                'email_verified_at' => now(),
                'city_id' => $city->id,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
            ]);
            $passengers[$key] = ['passenger' => Passenger::firstOrCreate(['user_id' => $user->id]), 'user' => $user, 'city' => $city];
        }

        return $passengers;
    }

    private function rides(array $drivers, array $locations): array
    {
        $definitions = [
            'campus_morning' => ['mira', 'to_institution', 'pending', now()->addDays(2)->setTime(7, 15), null, null],
            'campus_evening' => ['mira', 'from_institution', 'pending', now()->addDays(2)->setTime(17, 30), null, null],
            'full_commute' => ['rami', 'to_institution', 'active', now()->subMinutes(30), now()->subMinutes(25), null],
            'completed_return' => ['rami', 'from_institution', 'completed', now()->subDay()->setTime(17, 0), now()->subDay()->setTime(17, 3), now()->subDay()->setTime(18, 15)],
            'canceled_campus' => ['leila', 'to_institution', 'canceled', now()->subDay()->setTime(8, 0), null, null],
            'open_return' => ['leila', 'from_institution', 'pending', now()->addDays(3)->setTime(17, 0), null, null],
        ];

        $rides = [];
        foreach (['mira', 'rami', 'leila'] as $key) {
            $driver = $drivers[$key]['driver'];
            $group = RideGroup::where('driver_id', $driver->id)->first();
            if (! $group) {
                $locationGroup = LocationGroup::create();
                $group = RideGroup::create(['driver_id' => $driver->id, 'location_group_id' => $locationGroup->id]);
            }
            foreach ($locations as $location) {
                LocationGroupLocationRel::firstOrCreate([
                    'location_group_id' => $group->location_group_id,
                    'location_id' => $location->id,
                    'location_type' => $location->type === 'institution' ? 'institution' : 'passenger',
                ]);
            }
            $drivers[$key]['ride_group'] = $group;
        }

        foreach ($definitions as $key => [$driverKey, $type, $status, $scheduled, $started, $finished]) {
            $driver = $drivers[$driverKey];
            $rides[$key] = Ride::updateOrCreate(
                ['ride_group_id' => $drivers[$driverKey]['ride_group']->id, 'type' => $type],
                [
                    'vehicle_id' => $driver['vehicle']->id,
                    'scheduled_time' => $scheduled,
                    'start_time' => $started,
                    'finish_time' => $finished,
                    'status' => $status,
                    'booked_seats' => 0,
                    'available_seats' => $driver['vehicle']->capacity,
                ],
            );
        }

        return $rides;
    }

    private function request(array $passenger, ?Ride $toRide, ?Ride $fromRide, Location $institution, string $status, int $seats, string $notes): RideRequest
    {
        $city = $passenger['city'];

        return RideRequest::updateOrCreate(
            [
                'passenger_id' => $passenger['passenger']->id,
                'to_inst_ride_id' => $toRide?->id,
                'from_inst_ride_id' => $fromRide?->id,
            ],
            [
                'passenger_location_id' => $city->id,
                'passenger_latitude' => $city->latitude,
                'passenger_longitude' => $city->longitude,
                'institution_location_id' => $institution->id,
                'nb_seats_requested' => $seats,
                'notes' => $notes,
                'type' => $toRide && $fromRide ? 'round_trip' : 'one_way',
                'status' => $status,
            ],
        );
    }

    private function offer(RideRequest $request, Driver $driver, int $price, string $status, string $message, ?Location $suggestedStop = null): RideOffer
    {
        return RideOffer::updateOrCreate(
            ['ride_request_id' => $request->id, 'driver_id' => $driver->id],
            [
                'offered_price' => $price,
                'pickup_time' => $request->toInstRide?->scheduled_time ?? $request->fromInstRide?->scheduled_time,
                'driver_message' => $message,
                'suggested_pickup_location_id' => $suggestedStop?->id,
                'suggested_pickup_latitude' => $suggestedStop?->latitude,
                'suggested_pickup_longitude' => $suggestedStop?->longitude,
                'status' => $status,
            ],
        );
    }

    private function booking(RideRequest $request, Ride $ride, array $passenger, Location $institution, int $seats, int $price, string $status): Booking
    {
        $passengerModel = $passenger['passenger'];
        $city = $passenger['city'];
        $toInstitution = $ride->type === 'to_institution';
        $pickup = $toInstitution ? $city : $institution;
        $dropoff = $toInstitution ? $institution : $city;
        $existing = Booking::where('ride_id', $ride->id)->where('passenger_id', $passengerModel->id)->first();
        $node = $existing?->node ?? Node::create([
            'ride_id' => $ride->id,
            'pickup_latitude' => $pickup->latitude,
            'pickup_longitude' => $pickup->longitude,
            'dropoff_location_id' => $dropoff->id,
            'dropoff_latitude' => $dropoff->latitude,
            'dropoff_longitude' => $dropoff->longitude,
            'status' => 'pending',
        ]);
        $node->update([
            'pickup_location_id' => $pickup->id,
            'pickup_latitude' => $pickup->latitude,
            'pickup_longitude' => $pickup->longitude,
            'dropoff_location_id' => $dropoff->id,
            'dropoff_latitude' => $dropoff->latitude,
            'dropoff_longitude' => $dropoff->longitude,
            'status' => $ride->status === 'completed' ? 'completed' : 'pending',
        ]);

        $group = BookingGroup::firstOrCreate(['passenger_id' => $passengerModel->id]);

        return Booking::updateOrCreate(
            ['ride_id' => $ride->id, 'passenger_id' => $passengerModel->id],
            [
                'booking_group_id' => $group->id,
                'ride_request_id' => $request->id,
                'node_id' => $node->id,
                'nb_seats' => $seats,
                'price' => $price,
                'status' => $status,
            ],
        );
    }

    private function bookingsAndRequests(array $rides, array $drivers, array $passengers, array $locations): void
    {
        $campus = $locations['campus'];
        $liu = $locations['liu'];

        $roundTrip = $this->request($passengers['nadia'], $rides['campus_morning'], $rides['campus_evening'], $campus, 'accepted', 1, 'Daily campus commute with a return seat.');
        $this->offer($roundTrip, $drivers['mira']['driver'], 18, 'accepted', 'Morning pickup and evening return confirmed.');
        $this->booking($roundTrip, $rides['campus_morning'], $passengers['nadia'], $campus, 1, 9, 'active');
        $this->booking($roundTrip, $rides['campus_evening'], $passengers['nadia'], $campus, 1, 9, 'active');

        foreach ([
            ['youssef', 2, 16, 'Two seats for the morning lab.'],
            ['mariam', 1, 9, 'Pickup near the village center.'],
            ['bilal', 1, 10, 'One seat for the morning lecture.'],
        ] as [$key, $seats, $price, $notes]) {
            $request = $this->request($passengers[$key], $rides['campus_morning'], null, $campus, 'accepted', $seats, $notes);
            $this->offer($request, $drivers['mira']['driver'], $price, 'accepted', 'Seat confirmed on the campus shuttle.');
            $this->booking($request, $rides['campus_morning'], $passengers[$key], $campus, $seats, $price, 'active');
        }

        foreach ([
            ['maya', 2, 14],
            ['omar', 1, 8],
            ['lina', 1, 8],
        ] as [$key, $seats, $price]) {
            $request = $this->request($passengers[$key], $rides['full_commute'], null, $liu, 'accepted', $seats, 'Shared trip to LIU.');
            $this->offer($request, $drivers['rami']['driver'], $price, 'accepted', 'I will meet you at the village center.');
            $this->booking($request, $rides['full_commute'], $passengers[$key], $liu, $seats, $price, 'active');
        }

        foreach ([
            ['hadi', 5, 'Reliable ride home after class.'],
            ['sara', 4, 'Comfortable trip and an on-time arrival.'],
        ] as [$key, $score, $review]) {
            $request = $this->request($passengers[$key], null, $rides['completed_return'], $liu, 'accepted', 1, 'Return ride after the afternoon class.');
            $this->offer($request, $drivers['rami']['driver'], 10, 'accepted', 'Return pickup at the campus entrance.');
            $this->booking($request, $rides['completed_return'], $passengers[$key], $liu, 1, 10, 'active');
            Rating::updateOrCreate(
                ['rated_user_id' => $drivers['rami']['user']->id, 'rating_user_id' => $passengers[$key]['user']->id, 'ride_id' => $rides['completed_return']->id],
                ['rating' => $score, 'review_text' => $review],
            );
        }

        $canceled = $this->request($passengers['dana'], $rides['canceled_campus'], null, $campus, 'accepted', 1, 'The driver canceled this planned campus trip.');
        $this->offer($canceled, $drivers['leila']['driver'], 9, 'accepted', 'Pickup from Nabatieh.');
        $this->booking($canceled, $rides['canceled_campus'], $passengers['dana'], $campus, 1, 9, 'ride_canceled');

        $competing = $this->request($passengers['firas'], null, $rides['open_return'], $campus, 'driver_offered', 1, 'Compare drivers for a late return from campus.');
        $this->offer($competing, $drivers['leila']['driver'], 11, 'pending', 'Direct pickup at the main gate.');
        $this->offer($competing, $drivers['mira']['driver'], 9, 'pending', 'Meet at the central stop for a lower fare.', $locations['station']);

        $this->request($passengers['salma'], null, $rides['open_return'], $campus, 'pending', 2, 'Looking for two seats on the evening ride.');
        $rejected = $this->request($passengers['dana'], $rides['campus_morning'], null, $campus, 'rejected', 1, 'Requested after the route had nearly filled.');
        $this->offer($rejected, $drivers['mira']['driver'], 9, 'rejected', 'The proposed pickup time did not work.');
        $canceledRequest = $this->request($passengers['mariam'], null, $rides['campus_evening'], $campus, 'canceled', 1, 'Plans changed before accepting the offer.');
        $this->offer($canceledRequest, $drivers['mira']['driver'], 9, 'expired', 'Offer expired after the request was canceled.');
        $this->request($passengers['bilal'], null, $rides['open_return'], $campus, 'expired', 1, 'No driver accepted before the request expired.');

        foreach ($rides as $ride) {
            $bookedSeats = (int) $ride->bookings()->where('status', 'active')->sum('nb_seats');
            $ride->update([
                'booked_seats' => $bookedSeats,
                'available_seats' => $ride->vehicle->capacity - $bookedSeats,
            ]);
        }
    }

    private function templates(array $drivers): void
    {
        $definitions = [
            ['Showcase | Campus commute', 'mira', true, [
                ['to_institution', '07:15:00', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'], true],
                ['from_institution', '17:30:00', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'], true],
            ]],
            ['Showcase | Weekend service paused', 'leila', false, [
                ['to_institution', '09:00:00', ['saturday', 'sunday'], false],
            ]],
        ];

        foreach ($definitions as [$name, $driverKey, $active, $templates]) {
            $driver = $drivers[$driverKey]['driver'];
            $rideGroup = RideGroup::where('driver_id', $driver->id)->firstOrFail();
            $group = RideTemplateGroup::updateOrCreate(
                ['name' => $name, 'driver_id' => $driver->id],
                ['location_group_id' => $rideGroup->location_group_id, 'is_active' => $active],
            );
            foreach ($templates as [$type, $time, $days, $templateActive]) {
                RideTemplate::updateOrCreate(
                    ['ride_template_group_id' => $group->id, 'type' => $type],
                    [
                        'vehicle_id' => $drivers[$driverKey]['vehicle']->id,
                        'scheduled_time' => $time,
                        'recurring_days' => $days,
                        'is_active' => $templateActive,
                        'last_generated_at' => null,
                    ],
                );
            }
        }
    }
}
