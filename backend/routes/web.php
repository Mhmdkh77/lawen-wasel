<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\PassengerController;


// Login Routes
Route::middleware('guest')->prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'store'])->name('login.attempt');
});


// Admin Routes
Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Drivers
    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
    Route::get('/drivers/{user}', [DriverController::class, 'show'])->name('drivers.show');

    // Passengers
    Route::get('/passengers', [PassengerController::class, 'index'])->name('passengers.index');
    Route::get('/passengers/{user}', [PassengerController::class, 'show'])->name('passengers.show');

    // Rides
    Route::get('/rides', [PassengerController::class, 'index'])->name('rides.index');
    Route::get('/rides/{ride}', [PassengerController::class, 'show'])->name('rides.show');

    // Stations
    Route::get('/stations', [PassengerController::class, 'index'])->name('stations.index');
    Route::get('/stations/{station}', [PassengerController::class, 'show'])->name('stations.show');
});
