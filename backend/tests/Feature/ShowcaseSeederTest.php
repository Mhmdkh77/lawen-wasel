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
use Database\Seeders\ShowcaseSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

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

    $this->get(route('admin.rides.show', $morning))->assertOk()->assertSee('Route Map');
    $this->get(route('admin.ride-requests.show', $competing))->assertOk()->assertSee('Driver Offers');
    $this->get(route('admin.bookings.show', $outboundBooking))->assertOk();
    $this->get(route('admin.ride-template-groups.show', RideTemplateGroup::where('name', 'Showcase | Campus commute')->firstOrFail()))
        ->assertOk()->assertSee('Campus commute');
});

test('default seed includes the named showcase', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::where('email', 'showcase.mira@example.test')->exists())->toBeTrue()
        ->and(Ride::whereHas('vehicle', fn($query) => $query->where('plate_number', 'like', 'SHOW-%'))->count())->toBe(6);
});

test('showcase recurring templates generate one ride per direction for a scheduled day', function () {
    $this->seed(ShowcaseSeeder::class);

    $group = RideTemplateGroup::where('name', 'Showcase | Campus commute')->firstOrFail();
    foreach ($group->rideTemplates as $template) {
        $template->update(['recurring_days' => [strtolower(now()->format('l'))]]);
    }

    Artisan::call('app:generate-daily-rides');
    $todayRides = Ride::where('vehicle_id', $group->rideTemplates->first()->vehicle_id)
        ->whereDate('scheduled_time', today());
    expect($todayRides->count())->toBe(2);

    Artisan::call('app:generate-daily-rides');
    expect($todayRides->count())->toBe(2);
});
