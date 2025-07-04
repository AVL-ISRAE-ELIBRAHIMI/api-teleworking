<?php

use App\Http\Controllers\API\ProfilController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// routes/api.php
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'login']);
//Reservations
Route::get('/reservations/collaborateur', [ReservationController::class, 'index']);
//Profil
Route::get('/profil', [ProfilController::class, 'index']);
// Get availability
Route::get('/availability/month/{year}/{month}', [ReservationController::class, 'getMonthlyAvailability']);
Route::get('/availability/day/{date}', [ReservationController::class, 'getDailyAvailability']);

// Get resources
Route::get('/places', [ReservationController::class, 'getPlaces']);
Route::get('/salles', [ReservationController::class, 'getSalles']);

// Create reservations
Route::post('/reservations', [ReservationController::class, 'store']);

Route::get('/seat-booking-type', [ReservationController::class, 'getSeatBookingType']);

