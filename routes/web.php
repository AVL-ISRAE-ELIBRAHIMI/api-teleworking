<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

// ALL routes that need authentication - grouped together
Route::middleware(['web', \App\Http\Middleware\IisAuth::class])->group(function () {
    
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
            'session_started' => $request->session()->isStarted()
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
            \Log::info('=== API /user endpoint accessed ===');
            \Log::info('Auth check result: ' . (Auth::check() ? 'true' : 'false'));
            
            $user = Auth::user();
            \Log::info('Auth::user() result: ' . ($user ? 'User found - ' . $user->email : 'null'));
            
            if ($user) {
                $user->load('departement', 'equipe');
                \Log::info('User loaded with relations');
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
        Route::post('/reservations', [ReservationController::class, 'store']);
        Route::get('/seat-booking-type', [ReservationController::class, 'getSeatBookingType']);
    });

    // Catch-all route for Vue Router (SPA) 
    Route::get('/{any?}', function () {
        return view('welcome');
    })->where('any', '.*');
    
});