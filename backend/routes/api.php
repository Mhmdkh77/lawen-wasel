<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverRideController;
use App\Http\Controllers\Api\PassengerController;
use App\Http\Controllers\Api\PassengerRideController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\RideGroupController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\RideSearchController;
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
                Route::get('/ride-requests', 'rideRequests');
                Route::get('/ride-requests/{rideRequest}', 'rideRequest'); // to do

                Route::get('/ride-offers', 'rideOffers'); // To Do
                Route::get('/ride-offers/{RideOffer}', 'rideOffer'); // To Do
                Route::post('/ride-offers', 'sendOffer');

                Route::get('/rides', 'getRides');
                Route::post('/rides', 'createRide');
                Route::get('/rides/{ride}', 'getRide');
                Route::put('/rides/{ride}', 'updateRide');
            });
        });
    });


    // Passenger -------------------------------------------------------------
    Route::middleware(['passenger'])->prefix('passenger')->group(function () {


        Route::controller(PassengerRideController::class)->group(function () {
            Route::post('/search-rides', 'search');
            Route::post('/ride-requests', 'sendRideRequest');
            Route::get('/ride-requests',  'getRideRequests');
            Route::patch('/ride-requests/{rideRequest}/cencel',  'cancelRideRequest');
            Route::get('/bookings',  'getBookings');
            Route::patch('/bookings/{booking}/cencel',  'cancelBooking');
        });
    });


    Route::controller(RideGroupController::class)->prefix('ride-groups')->group(function () {
        Route::get('/{rideGroup}', 'show');
        // Route::put('/{rideGroup}',  'update');
        // Route::delete('/{rideGroup}',  'destroy');
    });
});



Route::get('/test', [TestController::class, 'index']);
