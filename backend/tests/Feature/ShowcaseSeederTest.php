<?php

use App\Models\Booking;
use App\Models\Admin;
use App\Models\Node;
use App\Models\Rating;
use App\Models\Ride;
use App\Models\RideOffer;
use App\Models\RideRequest;
use App\Models\RideTemplate;
use App\Models\RideTemplateGroup;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\LocationGroup;
use Database\Seeders\ShowcaseSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('showcase seed creates connected scenarios and can be run twice', function () {
    $this->seed(ShowcaseSeeder::class);

    $mira = User::where('email', 'showcase.mira@example.test')->firstOrFail()->driver;
    $rami = User::where('email', 'showcase.rami@example.test')->firstOrFail()->driver;
    $morning = Ride::where('ride_group_id', $mira->rideGroups()->firstOrFail()->id)
        ->where('type', 'to_institution')->firstOrFail();
    $return = Ride::where('ride_group_id', $mira->rideGroups()->firstOrFail()->id)
        ->where('type', 'from_institution')->firstOrFail();
    $full = Ride::where('ride_group_id', $rami->rideGroups()->firstOrFail()->id)
        ->where('type', 'to_institution')->firstOrFail();
    $completed = Ride::where('ride_group_id', $rami->rideGroups()->firstOrFail()->id)
        ->where('type', 'from_institution')->firstOrFail();

    expect($morning->bookings()->count())->toBe(4)
        ->and($morning->booked_seats)->toBe(5)
        ->and($morning->available_seats)->toBe(1)
        ->and($full->booked_seats)->toBe(4)
        ->and($full->available_seats)->toBe(0)
        ->and($completed->ratings()->count())->toBe(2);

    $nadia = User::where('email', 'showcase.nadia@example.test')->firstOrFail()->passenger;
    $roundTrip = RideRequest::where('passenger_id', $nadia->id)->where('type', 'round_trip')->firstOrFail();
    $outboundBooking = Booking::where('ride_id', $morning->id)->where('passenger_id', $nadia->id)->firstOrFail();
    $returnBooking = Booking::where('ride_id', $return->id)->where('passenger_id', $nadia->id)->firstOrFail();
    expect($roundTrip->to_inst_ride_id)->toBe($morning->id)
        ->and($roundTrip->from_inst_ride_id)->toBe($return->id)
        ->and($outboundBooking->booking_group_id)->toBe($returnBooking->booking_group_id);

    $competing = RideRequest::where('status', 'driver_offered')->firstOrFail();
    expect($competing->rideOffers()->count())->toBe(2);

    $counts = [
        User::count(), Ride::count(), RideRequest::count(), RideOffer::count(),
        Booking::count(), Node::count(), Rating::count(), RideTemplate::count(),
    ];
    $this->seed(ShowcaseSeeder::class);
    expect([
        User::count(), Ride::count(), RideRequest::count(), RideOffer::count(),
        Booking::count(), Node::count(), Rating::count(), RideTemplate::count(),
    ])->toBe($counts);

    $this->withoutVite();
    $this->actingAs(Admin::create([
        'name' => 'Showcase Tester',
        'email' => 'showcase-admin@example.test',
        'password' => 'password',
    ]), 'admin');
    config()->set('services.ors.key', null);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Upcoming rides')
        ->assertSee('Needs attention')
        ->assertSee('Mira Haddad')
        ->assertViewHas('overview', fn ($overview) => $overview['rides_in_progress'] >= 1
            && $overview['open_requests'] >= 1)
        ->assertViewHas('schedule', fn ($schedule) => $schedule->count() === 7);

    $this->get(route('admin.rides.show', $morning))->assertOk()->assertSee('Route Map');
    $this->get(route('admin.ride-requests.show', $competing))->assertOk()->assertSee('Driver Offers');
    $this->get(route('admin.bookings.show', $outboundBooking))->assertOk();
    $this->get(route('admin.ride-template-groups.show', RideTemplateGroup::where('name', 'Showcase | Campus commute')->firstOrFail()))
        ->assertOk()->assertSee('Campus commute');
});

test('default seed includes the named showcase', function () {
    $this->seed(DatabaseSeeder::class);

    $demoBookings = DB::table('bookings')
        ->join('rides', 'rides.id', '=', 'bookings.ride_id')
        ->join('vehicles', 'vehicles.id', '=', 'rides.vehicle_id')
        ->join('nodes', 'nodes.id', '=', 'bookings.node_id')
        ->join('ride_requests', 'ride_requests.id', '=', 'bookings.ride_request_id')
        ->join('locations as institutions', 'institutions.id', '=', 'ride_requests.institution_location_id')
        ->join('passengers', 'passengers.id', '=', 'bookings.passenger_id')
        ->join('users', 'users.id', '=', 'passengers.user_id')
        ->whereRaw('vehicles.plate_number NOT LIKE ?', ['SHOW-%']);

    expect(User::where('email', 'showcase.mira@example.test')->exists())->toBeTrue()
        ->and(Ride::whereHas('vehicle', fn($query) => $query->where('plate_number', 'like', 'SHOW-%'))->count())->toBe(6)
        ->and((clone $demoBookings)->count())->toBeGreaterThan(0)
        ->and((clone $demoBookings)->whereRaw("(
            (rides.type = 'to_institution' AND (
                ABS(nodes.pickup_latitude - users.latitude) > 0.000001 OR
                ABS(nodes.pickup_longitude - users.longitude) > 0.000001 OR
                ABS(nodes.dropoff_latitude - institutions.latitude) > 0.000001 OR
                ABS(nodes.dropoff_longitude - institutions.longitude) > 0.000001
            )) OR
            (rides.type = 'from_institution' AND (
                ABS(nodes.pickup_latitude - institutions.latitude) > 0.000001 OR
                ABS(nodes.pickup_longitude - institutions.longitude) > 0.000001 OR
                ABS(nodes.dropoff_latitude - users.latitude) > 0.000001 OR
                ABS(nodes.dropoff_longitude - users.longitude) > 0.000001
            ))
        )")->count())->toBe(0)
        ->and(DB::table('bookings')->join('rides', 'rides.id', '=', 'bookings.ride_id')
            ->where('rides.status', 'canceled')->where('bookings.status', 'active')->count())->toBe(0)
        ->and(DB::table('bookings')->where('status', '!=', 'active')->whereNotNull('node_id')->count())->toBe(0)
        ->and((clone $demoBookings)->where('ride_requests.type', 'round_trip')->count())->toBe(0)
        ->and(DB::table('ride_requests')->whereIn('status', ['pending', 'driver_offered'])
            ->where(function ($query) {
                $closedRideIds = Ride::where('status', '!=', 'pending')->pluck('id');
                $query->whereIn('to_inst_ride_id', $closedRideIds)
                    ->orWhereIn('from_inst_ride_id', $closedRideIds);
            })->count())->toBe(0);
});

test('recurring templates keep seven days of rides available without duplicates', function () {
    $this->travelTo(Carbon::parse('2026-10-16 06:00:00'));
    $this->seed(ShowcaseSeeder::class);

    $group = RideTemplateGroup::where('name', 'Showcase | Campus commute')->firstOrFail();
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    $templates = $group->rideTemplates;
    $templates[0]->update(['recurring_days' => $days, 'scheduled_time' => '10:00']);
    $templates[1]->update(['recurring_days' => $days, 'scheduled_time' => '18:00']);
    $extra = RideTemplate::create([
        'ride_template_group_id' => $group->id,
        'vehicle_id' => $templates[0]->vehicle_id,
        'type' => $templates[0]->type,
        'scheduled_time' => '11:00',
        'recurring_days' => $days,
        'is_active' => true,
    ]);
    $paused = RideTemplateGroup::where('name', 'Showcase | Weekend service paused')->firstOrFail()
        ->rideTemplates()->firstOrFail();
    $paused->update(['is_active' => true, 'recurring_days' => $days]);

    Artisan::call('app:generate-daily-rides');
    $generated = Ride::whereIn('ride_template_id', $templates->pluck('id')->push($extra->id));
    expect($generated->count())->toBe(21)
        ->and((clone $generated)->whereDate('scheduled_time', today()->addDays(6))->count())->toBe(3)
        ->and((clone $generated)->whereDate('scheduled_time', today()->addDays(7))->count())->toBe(0)
        ->and(Ride::where('ride_template_id', $paused->id)->count())->toBe(0);

    Artisan::call('app:generate-daily-rides');
    expect($generated->count())->toBe(21);

    $this->travelTo(Carbon::parse('2026-10-17 06:00:00'));
    Artisan::call('app:generate-daily-rides');
    expect($generated->count())->toBe(24);
});

test('creating a ride template immediately opens the seven day booking window', function () {
    $this->travelTo(Carbon::parse('2026-10-16 06:00:00'));
    $this->seed(ShowcaseSeeder::class);

    $existing = RideTemplateGroup::where('name', 'Showcase | Campus commute')->firstOrFail();
    Sanctum::actingAs(User::where('email', 'showcase.mira@example.test')->firstOrFail());

    $this->postJson('/api/driver/ride-template-groups', [
        'name' => 'Advance booking example',
        'ride_templates' => [[
            'vehicle_id' => $existing->rideTemplates->first()->vehicle_id,
            'scheduled_time' => '12:00',
            'recurring_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'type' => 'to_institution',
        ]],
        'locations' => $existing->locationGroup->locations->map(fn ($location) => [
            'id' => $location->id,
            'type' => $location->pivot->location_type,
        ])->all(),
    ])->assertOk();

    $newTemplate = RideTemplateGroup::where('name', 'Advance booking example')->firstOrFail()
        ->rideTemplates()->firstOrFail();
    expect(Ride::where('ride_template_id', $newTemplate->id)->count())->toBe(7);
});

test('constraints preserve rides and allow safe template group and vehicle deletion', function () {
    $this->seed(ShowcaseSeeder::class);

    $driver = User::where('email', 'showcase.mira@example.test')->firstOrFail();
    Sanctum::actingAs($driver);

    $group = RideTemplateGroup::where('name', 'Showcase | Campus commute')->firstOrFail();
    $locationGroupId = $group->location_group_id;
    $locationCount = $group->locationGroup->locations()->count();
    $templateIds = $group->rideTemplates()->pluck('id');
    $generatedRideIds = Ride::whereIn('ride_template_id', $templateIds)->pluck('id');

    $this->getJson("/api/driver/ride-template-groups/{$group->id}")
        ->assertOk()
        ->assertJsonPath('ride_template_group.id', $group->id);
    $this->deleteJson("/api/driver/ride-template-groups/{$group->id}")->assertOk();
    expect(RideTemplateGroup::whereKey($group->id)->exists())->toBeFalse()
        ->and(LocationGroup::whereKey($locationGroupId)->exists())->toBeTrue()
        ->and(LocationGroup::findOrFail($locationGroupId)->locations()->count())->toBe($locationCount)
        ->and(Ride::whereKey($generatedRideIds)->count())->toBe($generatedRideIds->count());

    $usedVehicle = $driver->driver->vehicles()->whereHas('rides')->firstOrFail();
    $this->deleteJson("/api/driver/vehicles/{$usedVehicle->id}")->assertStatus(409);
    expect(Vehicle::whereKey($usedVehicle->id)->exists())->toBeTrue();

    $unusedVehicle = $driver->driver->vehicles()->create([
        'plate_number' => 'SCHEMA-UNUSED',
        'brand' => 'Test',
        'color' => 'White',
        'capacity' => 4,
    ]);
    $this->deleteJson("/api/driver/vehicles/{$unusedVehicle->id}")->assertOk();
    expect(Vehicle::whereKey($unusedVehicle->id)->exists())->toBeFalse();
});

test('numbered checkpoints follow the optimized pickup and drop-off order', function () {
    $this->seed(ShowcaseSeeder::class);
    $this->withoutVite();
    $this->actingAs(Admin::create([
        'name' => 'Route Tester',
        'email' => 'route-admin@example.test',
        'password' => 'password',
    ]), 'admin');

    $ride = Ride::whereHas('vehicle', fn($query) => $query->where('plate_number', 'SHOW-101'))
        ->where('type', 'to_institution')->firstOrFail();
    $nodes = $ride->nodes()->orderByDesc('id')->get();
    $steps = [['type' => 'start']];
    foreach ($nodes as $node) {
        $steps[] = ['type' => 'pickup', 'id' => $node->id];
        $steps[] = ['type' => 'delivery', 'id' => $node->id];
    }
    Http::fake(fn() => Http::response(['routes' => [['steps' => $steps, 'geometry' => 'encoded-road-path']]]));
    config()->set('services.ors.key', 'test-key');

    $this->get(route('admin.rides.show', $ride))
        ->assertOk()
        ->assertViewHas('routeOptimized', true)
        ->assertViewHas('routeGeometry', 'encoded-road-path')
        ->assertViewHas('routeCheckpoints', fn($checkpoints) => count($checkpoints) === 9
            && $checkpoints[1]['node_id'] === $nodes->first()->id
            && $checkpoints[1]['kind'] === 'pickup'
            && $checkpoints[2]['node_id'] === $nodes->first()->id
            && $checkpoints[2]['kind'] === 'delivery'
            && $checkpoints[8]['number'] === 8)
        ->assertViewHas('orderedWaypoints', fn($waypoints) => count($waypoints) === 9);

    Http::assertSent(fn($request) => $request['options']['g'] === true);

    $this->get(route('admin.rides.show', $ride))->assertOk();
    Http::assertSentCount(1);

    $nodes->first()->update(['pickup_latitude' => 33.4]);
    $this->get(route('admin.rides.show', $ride))->assertOk();
    Http::assertSentCount(2);
});
