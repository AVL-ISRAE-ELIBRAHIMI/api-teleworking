<?php

// use App\Http\Controllers\API\ProfilController;
// use App\Http\Controllers\API\ReservationController;
// use App\Http\Controllers\AuthController;

use App\Http\Controllers\API\CollaborateurController;
use App\Http\Controllers\API\DepartementController;
use App\Http\Controllers\API\ReservationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// Welcome page
// Route::get('/', function () {
//     return view('welcome');
// });
// Public authentication routes (no middleware needed)
// Route::get('/login', [AuthController::class, 'login']); 
// Route::post('/auth/validate-user', [AuthController::class, 'validateUser']);
// Route::get('/auth/current-user', [AuthController::class, 'getCurrentUser']);
// Route::post('/auth/logout', [AuthController::class, 'logout']);
// Route::get('/csrf-token', function () {
//     return response()->json(['csrf_token' => csrf_token()]);
// });
// Routes that DON'T need authentication
Route::get('/test-db', function () {
    $accountName = env('DEV_IMPERSONATE_ACCOUNT');

    try {
        $user = \App\Models\Collaborateur::where('account_name', $accountName)->first();

        return [
            'account_name_searched' => $accountName,
            'user_found' => $user ? 'yes' : 'no',
            'user_data' => $user ? [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'email' => $user->email,
                'account_name' => $user->account_name
            ] : null,
            'total_users_in_db' => \App\Models\Collaborateur::count(),
            'users_with_account_names' => \App\Models\Collaborateur::whereNotNull('account_name')->count()
        ];
    } catch (\Exception $e) {
        return [
            'error' => $e->getMessage(),
            'account_name_searched' => $accountName
        ];
    }
});
// Manual middleware test (always works)
Route::get('/manual-auth', function () {
    $middleware = new \App\Http\Middleware\IisAuth();

    try {
        $result = $middleware->handle(request(), function ($request) {
            return response()->json([
                'middleware_executed' => true,
                'auth_check' => Auth::check(),
                'user' => Auth::user()
            ]);
        });

        return $result;
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});
// New authentication endpoints for your approach
// Route::get('/auth/company-users', [AuthController::class, 'fetchCompanyUsers']);
// Route::post('/auth/windows-authenticate', [AuthController::class, 'authenticateWithWindowsAndCompanyList']);
// Route::post('/auth/authenticate-account', [AuthController::class, 'authenticateWithAccountName']);

// // Protected routes - require authentication
// Route::middleware(['api.auth'])->group(function () {
//     // Reservations
// Route::get('/reservations/collaborateur', [ReservationController::class, 'index']);
// Route::get('/reservations/team', [ReservationController::class, 'index_for_team_leads']);
// Route::get('/reservations/skill-team', [ReservationController::class, 'index_for_skill_team_leads']);

//     // Profile
// Route::get('/profil', [ProfilController::class, 'index']);

// Get availability
// Route::get('/availability/month/{year}/{month}', [ReservationController::class, 'getMonthlyAvailability']);
// Route::get('/availability/day/{date}', [ReservationController::class, 'getDailyAvailability']);

// // Get resources
// Route::get('/places', [ReservationController::class, 'getPlaces']);
// Route::get('/salles', [ReservationController::class, 'getSalles']);

// // Create reservations
// Route::post('/reservations', [ReservationController::class, 'store']);
Route::middleware(['web', \App\Http\Middleware\LocalAuth::class])->group(function () {
    // Main app route
    Route::get('/', function () {
        return view('welcome');
    });

    // Debug routes
    Route::get('/debug-auth', function () {
        return response()->json([
            'env' => app()->environment(),
            'dev_account' => env('DEV_IMPERSONATE_ACCOUNT'),
            'auth_check' => Auth::check(),
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'middleware_applied' => 'yes',
            'timestamp' => now()
        ]);
    });

    Route::get('/debug-session', function (Request $request) {
        $sessionId = $request->session()->getId();
        $sessionData = $request->session()->all();

        return response()->json([
            'session_id' => $sessionId,
            'session_data' => $sessionData,
            'auth_check' => Auth::check(),
            'user' => Auth::user(),
            'has_session' => $request->hasSession(),
            'session_started' => $request->session()->isStarted(),
        ]);
    });

    Route::get('/test-middleware-approaches', function () {
        return response()->json([
            'middleware_group_test' => 'This route is in the middleware group',
            'auth_check' => Auth::check(),
            'user' => Auth::user()?->email,
            'timestamp' => now()
        ]);
    });

    // API routes
    Route::prefix('api')->group(function () {

        // Get current user endpoint
        Route::get('/user', function (Request $request) {

            $user = Auth::user();

            if ($user) {
                $user->load('departement', 'equipe', 'managerUser');
            }

            return response()->json($user);
        });

        // Logout endpoint
        Route::post('/logout', function (Request $request) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return response()->json(['message' => 'Logged out successfully']);
        });

        // Add reservation endpoints
        Route::get('/reservations/collaborateur', [ReservationController::class, 'index']);
        Route::get('/availability/month/{year}/{month}', [ReservationController::class, 'getMonthlyAvailability']);
        Route::get('/availability/day/{date}', [ReservationController::class, 'getDailyAvailability']);
        Route::get('/places', [ReservationController::class, 'getPlaces']);
        Route::get('/salles', [ReservationController::class, 'getSalles']);
        Route::middleware(['auth:sanctum'])->post('/reservations', [ReservationController::class, 'store']);
        Route::get('/seat-booking-type', [ReservationController::class, 'getSeatBookingType']);
        Route::get('/dashboard-type', [ReservationController::class, 'getDashboardType']);
        Route::get('/reservations/collaborateur', [ReservationController::class, 'index']);
        Route::get('/reservations/team', [ReservationController::class, 'index_for_team_leads']);
        Route::get('/reservations/skill-team', [ReservationController::class, 'index_for_skill_team_leads']);
        Route::get('/check-role', [ReservationController::class, 'is_STL']);
        Route::get('/check-user-role', [CollaborateurController::class, 'getUserRole']);
        Route::get('/check-user', [ReservationController::class, 'getUserData']);
        Route::get('/kpi', [DepartementController::class, 'reservationsStats']);
        Route::get('/kpi/stl', [DepartementController::class, 'reservationsStatsSTL']);
      


        Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
            $user = $request->user();
            return response()->json([
                'id' => $user->id,
                'role' => $user->getRoleNames()->first(), // Spatie
                'departement_id' => $user->departement_id
            ]);
        });

    
    });

    // Catch-all route for Vue Router (SPA) 
    Route::get('/{any?}', function () {
        return view('welcome');
    })->where('any', '.*');
});
