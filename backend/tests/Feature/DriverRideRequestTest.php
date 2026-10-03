<?php

use App\Models\Admin;
use App\Models\Driver;
use App\Models\Location;
use App\Models\LocationGroup;
use App\Models\Passenger;
use App\Models\Ride;
use App\Models\RideGroup;
use App\Models\RideOffer;
use App\Models\RideRequest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function rideRequestTestUser(string $name, string $role): User
{
    return User::create([
        'name' => $name,
        'email' => strtolower($name) . '@example.test',
        'password' => 'password',
        'phone' => '12345678',
        'role' => $role,
    ]);
}

function rideRequestTestRide(Driver $driver, string $type = 'to_institution'): Ride
{
    $vehicle = Vehicle::create([
        'driver_id' => $driver->id,
        'plate_number' => 'TEST' . $driver->id,
        'brand' => 'Test',
        'color' => 'Blue',
        'capacity' => 4,
    ]);

    $group = RideGroup::create([
        'driver_id' => $driver->id,
        'location_group_id' => LocationGroup::create()->id,
    ]);

    return Ride::create([
        'vehicle_id' => $vehicle->id,
        'ride_group_id' => $group->id,
        'scheduled_time' => now()->addDay(),
        'type' => $type,
        'available_seats' => 4,
        'status' => 'pending',
    ]);
}

beforeEach(function () {
    $institution = Location::create([
        'name' => 'Test University',
        'type' => 'institution',
        'latitude' => 33.9,
        'longitude' => 35.5,
    ]);

    $this->passenger = Passenger::create([
        'user_id' => rideRequestTestUser('Passenger', 'passenger')->id,
    ]);
    $this->owner = Driver::create([
        'user_id' => rideRequestTestUser('Owner', 'driver')->id,
        'is_verified' => true,
    ]);
    $this->otherDriver = Driver::create([
        'user_id' => rideRequestTestUser('Other', 'driver')->id,
        'is_verified' => true,
    ]);
    $this->ride = rideRequestTestRide($this->owner);
    $this->rideRequest = RideRequest::create([
        'passenger_id' => $this->passenger->id,
        'to_inst_ride_id' => $this->ride->id,
        'passenger_latitude' => 33.8,
        'passenger_longitude' => 35.4,
        'institution_location_id' => $institution->id,
        'nb_seats_requested' => 1,
        'type' => 'one_way',
        'status' => 'pending',
    ]);
});

test('the owner can view and accept a ride request', function () {
    Sanctum::actingAs($this->owner->user);

    $this->getJson('/api/driver/ride-requests/' . $this->rideRequest->id)
        ->assertOk()
        ->assertJsonPath('ride_request.to_inst_ride.id', $this->ride->id);

    $this->patchJson('/api/driver/ride-requests/' . $this->rideRequest->id . '/accept')
        ->assertOk();

    $this->assertDatabaseHas('ride_requests', ['id' => $this->rideRequest->id, 'status' => 'accepted']);
    $this->assertDatabaseHas('bookings', ['ride_request_id' => $this->rideRequest->id, 'ride_id' => $this->ride->id]);
    $this->assertDatabaseHas('rides', ['id' => $this->ride->id, 'available_seats' => 3]);

    $this->patchJson('/api/driver/ride-requests/' . $this->rideRequest->id . '/reject')
        ->assertStatus(400);
});

test('another driver cannot view or accept a ride request', function () {
    rideRequestTestRide($this->otherDriver);
    Sanctum::actingAs($this->otherDriver->user);

    $this->getJson('/api/driver/ride-requests/' . $this->rideRequest->id)->assertForbidden();
    $this->patchJson('/api/driver/ride-requests/' . $this->rideRequest->id . '/accept')->assertForbidden();

    $this->assertDatabaseHas('ride_requests', ['id' => $this->rideRequest->id, 'status' => 'pending']);
    $this->assertDatabaseCount('bookings', 0);
});

test('a driver cannot accept a round trip containing another drivers ride', function () {
    $returnRide = rideRequestTestRide($this->otherDriver, 'from_institution');
    $this->rideRequest->update([
        'from_inst_ride_id' => $returnRide->id,
        'type' => 'round_trip',
    ]);
    Sanctum::actingAs($this->owner->user);

    $this->getJson('/api/driver/ride-requests')
        ->assertOk()
        ->assertJsonCount(0, 'ride_requests');
    $this->patchJson('/api/driver/ride-requests/' . $this->rideRequest->id . '/accept')->assertForbidden();

    $this->assertDatabaseCount('bookings', 0);
    $this->assertDatabaseHas('rides', ['id' => $returnRide->id, 'available_seats' => 4]);
});

test('a passenger can see offers from multiple drivers on one request', function () {
    foreach ([$this->owner, $this->otherDriver] as $driver) {
        RideOffer::create([
            'ride_request_id' => $this->rideRequest->id,
            'driver_id' => $driver->id,
            'offered_price' => 10,
            'status' => 'pending',
        ]);
    }
    Sanctum::actingAs($this->passenger->user);

    $this->getJson('/api/passenger/ride-requests/' . $this->rideRequest->id)
        ->assertOk()
        ->assertJsonCount(2, 'ride_offers');

    $this->withoutVite();
    $this->actingAs(Admin::create([
        'name' => 'Test Admin',
        'email' => 'admin@example.test',
        'password' => 'password',
    ]), 'admin');

    $this->get(route('admin.ride-requests.show', $this->rideRequest))
        ->assertOk()
        ->assertSee('Owner')
        ->assertSee('Other');
});
