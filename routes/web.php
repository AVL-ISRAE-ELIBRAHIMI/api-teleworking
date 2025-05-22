<?php

use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

// routes/api.php
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login',[AuthController ::class, 'login']);
//Reservations
Route::get('/reservations/collaborateur', [ReservationController::class, 'index']);
