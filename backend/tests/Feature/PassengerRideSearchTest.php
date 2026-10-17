<?php

use App\Models\Driver;
use App\Models\Location;
use App\Models\LocationGroup;
use App\Models\LocationGroupLocationRel;
use App\Models\Ride;
use App\Models\RideGroup;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('ride search matches the pickup city and only returns bookable outbound rides', function () {
    Http::fake();

    $cityA = Location::create(['name' => 'City A', 'type' => 'city', 'latitude' => 33.4000, 'longitude' => 35.5000]);
    $cityB = Location::create(['name' => 'City B', 'type' => 'city', 'latitude' => 34.1000, 'longitude' => 35.5000]);
    $stopA = Location::create(['name' => 'City A stop', 'type' => 'station', 'city_id' => $cityA->id, 'latitude' => 33.4010, 'longitude' => 35.5010]);
    $institution = Location::create(['name' => 'Campus', 'type' => 'institution', 'latitude' => 33.4050, 'longitude' => 35.5050]);
    $otherInstitution = Location::create(['name' => 'Other campus', 'type' => 'institution', 'latitude' => 33.4060, 'longitude' => 35.5060]);

    $driverUser = User::create(['name' => 'Driver', 'email' => 'driver-search@example.test', 'phone' => '70000001', 'password' => 'password', 'role' => 'driver']);
    $driver = Driver::create(['user_id' => $driverUser->id, 'is_verified' => true]);
    $vehicle = Vehicle::create(['driver_id' => $driver->id, 'plate_number' => 'SEARCH-1', 'brand' => 'Toyota', 'color' => 'White', 'capacity' => 4]);
    $passenger = User::create(['name' => 'Passenger', 'email' => 'passenger-search@example.test', 'phone' => '70000002', 'password' => 'password', 'role' => 'passenger']);
    Sanctum::actingAs($passenger);

    $departure = now()->addDays(2)->setTime(8, 0, 0);
    $makeRide = function (Location $pickup, string $status = 'pending', int $hour = 8, ?Location $destination = null) use ($driver, $vehicle, $institution, $departure) {
        $destination ??= $institution;
        $locations = LocationGroup::create();
        LocationGroupLocationRel::create(['location_group_id' => $locations->id, 'location_id' => $pickup->id, 'location_type' => 'passenger']);
        LocationGroupLocationRel::create(['location_group_id' => $locations->id, 'location_id' => $destination->id, 'location_type' => 'institution']);
        $group = RideGroup::create(['driver_id' => $driver->id, 'location_group_id' => $locations->id]);
        Ride::create(['ride_group_id' => $group->id, 'vehicle_id' => $vehicle->id, 'scheduled_time' => $departure->copy()->setHour($hour), 'type' => 'to_institution', 'status' => $status, 'available_seats' => 4]);
        return $group;
    };

    $matching = $makeRide($stopA);
    $otherCity = $makeRide($cityB);
    $makeRide($stopA, 'pending', 12);
    $makeRide($stopA, 'canceled');
    $makeRide($stopA, 'pending', 8, $otherInstitution);

    $search = function (float $latitude, float $longitude) use ($institution, $departure) {
        return $this->postJson('/api/passenger/search-rides', [
            'passenger_latitude' => $latitude,
            'passenger_longitude' => $longitude,
            'institution_location_id' => $institution->id,
            'arrival_time' => $departure->toDateTimeString(),
            'nb_seats' => 1,
        ]);
    };

    $search(33.4002, 35.5002)->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', $matching->id)->assertJsonCount(1, '0.rides');
    $search(34.1002, 35.5002)->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', $otherCity->id);
    $search(0.0, 0.0)->assertStatus(422)->assertJsonPath('message', 'Pickup location is outside the supported cities.');

    $roundTripSearch = [
        'passenger_latitude' => 33.4002,
        'passenger_longitude' => 35.5002,
        'institution_location_id' => $institution->id,
        'arrival_time' => $departure->toDateTimeString(),
        'return_time' => $departure->copy()->setTime(17, 0)->toDateTimeString(),
        'nb_seats' => 1,
    ];
    $this->postJson('/api/passenger/search-rides', $roundTripSearch)->assertOk()->assertJsonCount(0);
    Ride::create([
        'ride_group_id' => $matching->id,
        'vehicle_id' => $vehicle->id,
        'scheduled_time' => $roundTripSearch['return_time'],
        'type' => 'from_institution',
        'status' => 'pending',
        'available_seats' => 4,
    ]);
    $this->postJson('/api/passenger/search-rides', $roundTripSearch)->assertOk()->assertJsonCount(1);
    Http::assertNothingSent();
});
