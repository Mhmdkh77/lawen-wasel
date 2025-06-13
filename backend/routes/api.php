<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PassengerController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\RideGroupController;
use App\Http\Controllers\Api\RideRequestController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\RideSearchController;
use Illuminate\Support\Facades\Route;



Route::prefix('register')->controller(RegistrationController::class)->group(function () {
    Route::post('/start', 'start');
    Route::post('/verify-email', 'verifyEmail');
    Route::post('/add-phone', 'addPhone');
    Route::post('/verify-phone', 'verifyPhone');
    Route::post('/finalize', 'finalize');
});

Route::post("/login", [AuthController::class, "login"]);

Route::middleware('auth:sanctum')->group(function () {

    // Authentication
    Route::controller(AuthController::class)->group(function () {
        Route::post("/logout", "logout");
        Route::post('/change-password',  'changePass');

        // Forget Pass Routes
        Route::post('/fpassword/code/send', 'sendResetCode');
        Route::post('/fpassword/code/verify',  'verifyResetCode');
        Route::post('/fpassword/code/reset',  'resetPassword');
    });

    // User
    Route::controller(UserController::class)->group(function () {
        Route::post("/user",  "getUser");
        Route::post('/user/location', 'updateLocation');
        Route::post('driver/licence', 'submitLicense');
    });

    // Vehicle
    Route::controller(VehicleController::class)->prefix('driver/vehicles')->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{vehicle}', 'show');
        Route::put('/{vehicle}', 'update');
        Route::delete('/{vehicle}', 'destroy');
    });

    Route::post('/search-rides', [RideSearchController::class, 'search']);

    Route::controller(PassengerController::class)->prefix('passenger')->group(function () {
        Route::post('/ride-requests', 'sendRideRequest');
        Route::get('/ride-requests',  'getRideRequests');
        Route::patch('/ride-requests/{rideRequest}/cencel',  'cancelRideRequest');
        Route::get('/bookings',  'getBookings');
        Route::patch('/bookings/{booking}/cencel',  'cancelBooking');
    });


    Route::controller(RideGroupController::class)->prefix('ride-groups')->group(function () {
        Route::get('/{rideGroup}', 'show');
        // Route::put('/{rideGroup}',  'update');
        // Route::delete('/{rideGroup}',  'destroy');
    });
});



Route::get('/test', [TestController::class, 'index']);
