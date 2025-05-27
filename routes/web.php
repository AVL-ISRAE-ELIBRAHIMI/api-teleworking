<?php

use App\Http\Controllers\API\ProfilController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// routes/api.php
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login',[AuthController ::class, 'login']);
//Reservations
Route::get('/reservations/collaborateur', [ReservationController::class, 'index']);
//Profil
Route::get('/profil', [ProfilController::class, 'index']);
