<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Your main application routes are defined here. The IisAuth middleware
| will automatically protect them.
|
*/

Route::get('/', function () {
    // The IisAuth middleware has already run. If we reach this point,
    // the user is authenticated.
    return view('welcome'); // Your main Vue app view
});

// An API endpoint for the frontend to get the logged-in user's details.
Route::get('/api/user', function (Request $request) {
    // Auth::user() will return the authenticated Collaborateur model.
    // The middleware handles the case where the user is not authenticated.
    return Auth::user();
});

// A simple logout route.
Route::post('/api/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return response()->json(['message' => 'Logged out successfully']);
});


// You can keep your other application-specific API routes.
// The middleware will ensure they are protected.
Route::middleware('auth:web')->prefix('api')->group(function () {
    // Example:
    // Route::get('/reservations', [ReservationController::class, 'index']);
});

