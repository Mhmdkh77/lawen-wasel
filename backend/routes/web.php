<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\PassengerController;
use App\Http\Controllers\Admin\RatingController;
use App\Http\Controllers\Admin\RideController;
use App\Http\Controllers\Admin\RideOfferController;
use App\Http\Controllers\Admin\RideRequestController;
use App\Http\Controllers\Admin\RideTemplateGroupController;
use App\Http\Controllers\Admin\VehicleController;

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Login Routes
Route::middleware('guest:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'store'])->name('login.attempt');
});


// Admin Routes
Route::middleware(['auth:admin', 'admin-active'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::delete('logout', [AdminLoginController::class, 'destroy'])->name('logout');

    // Passengers
    Route::get('/passengers', [PassengerController::class, 'index'])->name('passengers.index');
    Route::get('/passengers/{user}', [PassengerController::class, 'show'])->name('passengers.show');

    // Drivers
    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
    Route::get('/drivers/{user}', [DriverController::class, 'show'])->name('drivers.show');
    Route::post('/drivers/{driver}/toggle-verification', [DriverController::class, 'toggleVerification'])->name('drivers.toggle-verification');

    // Rides
    Route::get('/rides', [RideController::class, 'index'])->name('rides.index');
    Route::get('/rides/{ride}', [RideController::class, 'show'])->name('rides.show');

    // Vehicles
    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');

    // Ride Requests
    Route::get('/ride-requests', [RideRequestController::class, 'index'])->name('ride-requests.index');
    Route::get('/ride-requests/{rideRequest}', [RideRequestController::class, 'show'])->name('ride-requests.show');

    // Ride Offers
    Route::get('/ride-offers', [RideOfferController::class, 'index'])->name('ride-offers.index');
    Route::get('/ride-offers/{rideOffer}', [RideOfferController::class, 'show'])->name('ride-offers.show');

    // Ride Template Groups
    Route::get('/ride-template-groups', [RideTemplateGroupController::class, 'index'])->name('ride-template-groups.index');
    Route::get('/ride-template-groups/{rideTemplateGroup}', [RideTemplateGroupController::class, 'show'])->name('ride-template-groups.show');

    // Ratings
    Route::get('/ratings', [RatingController::class, 'index'])->name('ratings.index');

    Route::get('/account', [AdminProfileController::class, 'show'])->name('account.show');
    Route::patch('/account', [AdminProfileController::class, 'updateDetails'])->name('account.update');
    Route::patch('/account/password', [AdminProfileController::class, 'updatePassword'])->name('account.password');

    Route::middleware('super-admin')->group(function () {
        Route::get('/admins', [AdminUserController::class, 'index'])->name('admins.index');
        Route::get('/admins/create', [AdminUserController::class, 'create'])->name('admins.create');
        Route::post('/admins', [AdminUserController::class, 'store'])->name('admins.store');
        Route::get('/admins/{admin}', [AdminUserController::class, 'show'])->name('admins.show');
        Route::patch('/admins/{admin}/status', [AdminUserController::class, 'toggleStatus'])->name('admins.status');
        Route::patch('/admins/{admin}/role', [AdminUserController::class, 'toggleRole'])->name('admins.role');
    });

    // Locations
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::get('/locations/create', [LocationController::class, 'create'])->name('locations.create');
    Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
    Route::get('/locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
    Route::put('/locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
});
