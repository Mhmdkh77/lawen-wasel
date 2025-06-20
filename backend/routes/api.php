<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverRideController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\PassengerRideController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\RideTemplateGroupController;
use Illuminate\Support\Facades\Route;



Route::prefix('register')->controller(RegistrationController::class)->group(function () {
    // Route::post('/set-location', 'setLocation');
    Route::post('/start', 'start');
    Route::post('/verify-email', 'verifyEmail');
    Route::post('/resend-verify-email', 'resendVerifyEmail');
    // Route::post('/add-phone', 'addPhone');
    // Route::post('/verify-phone', 'verifyPhone');
    Route::post('/finalize', 'finalize');
});



Route::controller(AuthController::class)->group(function () {
    Route::post("/login", "login");

    // Forget Pass Routes
    Route::post('/fpassword/code/send', 'sendResetCode');
    Route::post('/fpassword/code/resend', 'resendResetCode');
    Route::post('/fpassword/code/verify',  'verifyResetCode');
    Route::post('/fpassword/code/reset',  'resetPassword');
});

Route::middleware('auth:sanctum')->group(function () {

    // User
    Route::controller(UserController::class)->group(function () {
        Route::post("/user",  "getUser");
        Route::put("/user",  "updateUser");
        // Route::post('/user/location', 'updateLocation');
        Route::post('/user/change-password',  'changePass');
        Route::post("/user/logout", "logout");
    });

    // Location
    Route::controller(LocationController::class)->prefix('locations')->group(function () {
        Route::get('/', 'getLocation');
    });

    // Driver ---------------------------------------------------------------
    Route::middleware(['driver'])->prefix('driver')->group(function () {

        // Vehicle
        Route::controller(VehicleController::class)->prefix('/vehicles')->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{vehicle}', 'show');
            Route::put('/{vehicle}', 'update');
            Route::delete('/{vehicle}', 'destroy');
        });

        Route::middleware(['driver-verified'])->group(function () {

            Route::controller(RideTemplateGroupController::class)->prefix('ride-template-groups')->group(function () {
                Route::get('/', 'index');
                Route::post('/',  'store');
                Route::get('/{RideTemplateGroup}',  'show');
                Route::put('/{RideTemplateGroup}',  'update');
                Route::delete('/{RideTemplateGroup}',  'destroy');
            });

            Route::controller(DriverRideController::class)->group(function () {
                // Ride Request
                Route::get('/ride-requests', 'rideRequests');
                Route::get('/ride-requests/{rideRequest}', 'rideRequest');
                Route::patch('/ride-requests/{rideRequest}/reject', 'rejectRideRequest');

                // Ride Offer
                Route::get('/ride-offers', 'rideOffers');
                Route::post('/ride-offers', 'sendOffer');
                Route::get('/ride-offers/{rideOffer}', 'rideOffer');
                Route::put('/ride-offers/{rideOffer}', 'editRideOffer'); // To Do -----------

                // Rides
                Route::get('/rides', 'getRides');
                Route::post('/rides', 'createRide');
                Route::get('/rides/{ride}', 'getRide');
                Route::put('/rides/{ride}', 'updateRide'); // To Do ------------
                Route::patch('/rides/{ride}/start', 'startRide');
                Route::patch('/rides/{ride}/finish', 'finishRide');
            });
        });
    });


    // Passenger -------------------------------------------------------------
    Route::middleware(['passenger'])->prefix('passenger')->group(function () {

        Route::controller(PassengerRideController::class)->group(function () {
            // Ride Groups
            Route::post('/search-rides', 'search');
            Route::get('ride-groups/{rideGroup}', 'showRideGroup');

            // Ride Requests
            Route::post('/ride-requests', 'sendRideRequest');
            Route::get('/ride-requests',  'getRideRequests');
            Route::get('/ride-requests/{rideRequest}',  'getRideRequest');
            Route::put('/ride-requests/{rideRequest}',  'editRideRequest');
            Route::patch('/ride-requests/{rideRequest}/cencel',  'cancelRideRequest');

            // Ride Offers
            Route::get('/ride-offers', 'getRideOffers');
            Route::get('/ride-offers/{rideOffer}', 'getRideOffer');
            Route::patch('/ride-offers/{rideOffer}/accept', 'acceptRideOffer');
            Route::patch('/ride-offers/{rideOffer}/reject', 'rejectRideOffer');

            // Bookings
            Route::get('/bookings',  'getBookings');
            Route::get('/bookings/{booking}',  'getBooking');
            Route::patch('/bookings/{booking}/cencel',  'cancelBooking');

            // Station
            Route::get('/stations', 'getStations'); // To Do ------------
            Route::get('/stations/{station}', 'getStation'); // To Do ------------
        });
    });
});



Route::get('/test', [TestController::class, 'index']);
