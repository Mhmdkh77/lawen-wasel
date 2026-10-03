<?php

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the admin dashboard and index pages render', function () {
    $this->withoutVite();
    $this->actingAs(Admin::create([
        'name' => 'Test Admin',
        'email' => 'admin@example.test',
        'password' => 'password',
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
        'admin.admins.index',
    ] as $route) {
        $this->get(route($route))->assertOk();
    }
});
