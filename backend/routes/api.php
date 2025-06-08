<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/rides/{ride}', [RideController::class, 'show']);
//     Route::post('/rides/start', [RideController::class, 'startRide']);
//     Route::post('/rides/update-location', [RideController::class, 'updateLocation']);
//     // Add more as needed
// });