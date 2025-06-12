<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RideController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\RideGroupController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;



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


    // Ride Group
    Route::controller(RideGroupController::class)->prefix('ride-groups')->group(function () {
        Route::get('/search/one_way', 'searchOneWay');
        Route::get('/search/round_trip', 'searchRoundTrip');
        Route::get('/{rideGroup}', 'show');
        Route::put('/{rideGroup}',  'update');
        Route::delete('/{rideGroup}',  'destroy');
    });

    // Ride
    Route::controller(RideController::class)->prefix('rides')->group(function () {
        Route::get('/{ride}',  'show');
        Route::post('/{ride}/book',  'book');
    });
});



Route::get('/test', [TestController::class, 'index']);
