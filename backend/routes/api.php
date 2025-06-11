<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\RideController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\VehicleController;
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
        Route::post("/user",  "getUser");
        Route::post("/logout", "logout");
        Route::post('/change-password',  'changePass');

        // Forget Pass Routes
        Route::post('/fpassword/code/send', 'sendResetCode');
        Route::post('/fpassword/code/verify',  'verifyResetCode');
        Route::post('/fpassword/code/reset',  'resetPassword');
    });

    // User
    Route::controller(UserController::class)->group(function () {
        Route::post('/user/location', 'updateLocation');
        Route::post('/driver/licence', 'submitLicense');
    });


    // Vehicle
    Route::controller(VehicleController::class)->group(function () {});


    // Ride
    Route::get('/rides', [RideController::class, 'index']);
    Route::get('/rides/{id}', [RideController::class, 'show']);
    Route::post('/rides/{id}/book', [RideController::class, 'book']);
});



Route::get('/test', [TestController::class, 'index']);
