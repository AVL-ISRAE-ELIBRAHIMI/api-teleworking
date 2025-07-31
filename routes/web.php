<?php

use App\Http\Controllers\API\ProfilController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Public authentication routes (no middleware needed)
Route::get('/login', [AuthController::class, 'login']); 
Route::post('/auth/validate-user', [AuthController::class, 'validateUser']);
Route::get('/auth/current-user', [AuthController::class, 'getCurrentUser']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

// New authentication endpoints for your approach
Route::get('/auth/company-users', [AuthController::class, 'fetchCompanyUsers']);
Route::post('/auth/windows-authenticate', [AuthController::class, 'authenticateWithWindowsAndCompanyList']);
Route::post('/auth/authenticate-account', [AuthController::class, 'authenticateWithAccountName']);

// Protected routes - require authentication
Route::middleware(['api.auth'])->group(function () {
    // Reservations
    Route::get('/reservations/collaborateur', [ReservationController::class, 'index']);
    
    // Profile
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
});