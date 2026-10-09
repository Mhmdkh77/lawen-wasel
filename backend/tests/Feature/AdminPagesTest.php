<?php

use App\Models\Admin;
use App\Models\Driver;
use App\Models\Location;
use App\Models\LocationGroup;
use App\Models\Node;
use App\Models\Ride;
use App\Models\RideGroup;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('the admin dashboard and index pages render', function () {
    $this->withoutVite();
    $this->actingAs(Admin::create([
        'name' => 'Test Admin',
        'email' => 'admin@example.test',
        'password' => 'password',
        'is_super_admin' => true,
    ]), 'admin');

    foreach ([
        'admin.dashboard',
        'admin.drivers.index',
        'admin.passengers.index',
        'admin.vehicles.index',
        'admin.rides.index',
        'admin.bookings.index',
        'admin.ride-requests.index',
        'admin.ride-offers.index',
        'admin.ride-template-groups.index',
        'admin.ratings.index',
        'admin.locations.index',
        'admin.account.show',
        'admin.admins.index',
    ] as $route) {
        $this->get(route($route))->assertOk();
    }
});

test('ride details render when route optimization cannot connect', function () {
    $this->withoutVite();
    $this->actingAs(Admin::create([
        'name' => 'Test Admin',
        'email' => 'admin@example.test',
        'password' => 'password',
    ]), 'admin');

    $driverUser = User::create([
        'name' => 'Test Driver',
        'email' => 'driver@example.test',
        'password' => 'password',
        'phone' => '12345678',
        'role' => 'driver',
        'latitude' => 33.9,
        'longitude' => 35.5,
    ]);
    $driver = Driver::create(['user_id' => $driverUser->id, 'is_verified' => true]);
    $vehicle = Vehicle::create([
        'driver_id' => $driver->id,
        'plate_number' => 'ROUTE1',
        'brand' => 'Test',
        'color' => 'Blue',
        'capacity' => 4,
    ]);
    $group = RideGroup::create([
        'driver_id' => $driver->id,
        'location_group_id' => LocationGroup::create()->id,
    ]);
    $ride = Ride::create([
        'vehicle_id' => $vehicle->id,
        'ride_group_id' => $group->id,
        'scheduled_time' => now()->addDay(),
        'type' => 'to_institution',
        'available_seats' => 4,
        'status' => 'pending',
    ]);
    $destination = Location::create([
        'name' => 'Test University',
        'type' => 'institution',
        'latitude' => 33.7,
        'longitude' => 35.3,
    ]);
    Node::create([
        'ride_id' => $ride->id,
        'pickup_latitude' => 33.8,
        'pickup_longitude' => 35.4,
        'dropoff_location_id' => $destination->id,
        'dropoff_latitude' => 33.7,
        'dropoff_longitude' => 35.3,
        'status' => 'pending',
    ]);

    config()->set('services.ors.key', 'test-key');
    $attempts = 0;
    Http::fake(function ($request) use (&$attempts) {
        $attempts++;
        expect($request->url())->toBe('https://api.heigit.org/vroom/v0');
        throw new ConnectionException('Service unavailable');
    });

    $this->get(route('admin.rides.show', $ride))
        ->assertOk()
        ->assertSee('Route Map')
        ->assertSee('Driver checkpoints')
        ->assertViewHas('routeOptimized', false)
        ->assertViewHas('routeGeometry', null)
        ->assertViewHas('routeCheckpoints', fn($checkpoints) => count($checkpoints) === 3
            && $checkpoints[1]['kind'] === 'pickup'
            && $checkpoints[1]['number'] === 1
            && $checkpoints[2]['kind'] === 'delivery')
        ->assertViewHas('orderedWaypoints', [
            ['lat' => 33.9, 'lng' => 35.5],
            ['lat' => 33.8, 'lng' => 35.4],
            ['lat' => 33.7, 'lng' => 35.3],
        ]);

    $this->get(route('admin.rides.show', $ride))->assertOk();
    expect($attempts)->toBe(1);
});
