<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\PassengerController;
use App\Http\Controllers\Admin\RideController;
use App\Http\Controllers\Admin\StationController;

// Login Routes
Route::middleware('guest:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'store'])->name('login.attempt');
});


// Admin Routes
Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::delete('logout', [AdminLoginController::class, 'destroy'])->name('logout');

    // Passengers
    Route::get('/passengers', [PassengerController::class, 'index'])->name('passengers.index');
    Route::get('/passengers/{user}', [PassengerController::class, 'show'])->name('passengers.show');

    // Drivers
    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
    Route::get('/drivers/{user}', [DriverController::class, 'show'])->name('drivers.show');


    // Rides
    Route::get('/rides', [RideController::class, 'index'])->name('rides.index');
    Route::get('/rides/{ride}', [RideController::class, 'show'])->name('rides.show');

    // Stations
    Route::get('/stations', [StationController::class, 'index'])->name('stations.index');
    Route::get('/stations/{station}', [StationController::class, 'show'])->name('stations.show');
});
