<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Models\Collaborateur;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IisAuth
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('=== IisAuth Middleware Start ===');
        Log::info('Request URI: ' . $request->getUri());
        Log::info('Environment: ' . App::environment());
        Log::info('Session ID: ' . $request->session()->getId());
        
        // === DEVELOPMENT MODE ===
        if (App::environment('local')) {
            Log::info('Running in LOCAL development mode');
            
            $accountName = env('DEV_IMPERSONATE_ACCOUNT');
            Log::info('DEV_IMPERSONATE_ACCOUNT: ' . ($accountName ?: 'NULL'));

            if (!$accountName) {
                Log::error('DEV_IMPERSONATE_ACCOUNT not set');
                abort(500, 'DEV_IMPERSONATE_ACCOUNT not configured in .env file');
            }

            // Check if already authenticated
            if (Auth::guard('web')->check()) {
                $currentUser = Auth::guard('web')->user();
                if ($currentUser->account_name === $accountName) {
                    Log::info('User already authenticated with correct account: ' . $currentUser->email);
                    return $next($request);
                } else {
                    Log::info('User authenticated but wrong account, re-authenticating');
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }

            try {
                Log::info('Searching for user with account_name: ' . $accountName);
                
                $user = Collaborateur::where('account_name', $accountName)->first();
                
                if (!$user) {
                    Log::error('User NOT found in database');
                    abort(403, "User with account_name '{$accountName}' not found in database.");
                }
                
                Log::info('User found! Email: ' . $user->email . ', ID: ' . $user->id);
                
                // Login the user
                Auth::guard('web')->login($user, false);
                $request->session()->regenerate();
                
                Log::info('Auth::login() called successfully');
                
                // Verify login worked
                if (Auth::guard('web')->check()) {
                    Log::info('✓ Authentication successful! User is now logged in');
                    Log::info('Session data after login: ' . json_encode($request->session()->all()));
                } else {
                    Log::error('✗ Authentication FAILED! Auth::login() did not work');
                }
                
                return $next($request);
                
            } catch (\Exception $e) {
                Log::error('Exception during authentication: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                abort(500, 'Authentication error: ' . $e->getMessage());
            }
        }

        // === PRODUCTION MODE (IIS) ===
        $windowsUser = $request->server('REMOTE_USER');
        Log::info('REMOTE_USER: ' . ($windowsUser ?: 'NULL'));

        if (!$windowsUser) {
            abort(401, 'No Windows user provided by IIS');
        }

        $accountName = $this->parseAccountName($windowsUser);
        
        // Check if already authenticated with correct account
        if (Auth::guard('web')->check()) {
            $currentUser = Auth::guard('web')->user();
            if ($currentUser->account_name === $accountName) {
                return $next($request);
            }
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        
        try {
            $collaborateur = Collaborateur::where('account_name', $accountName)->firstOrFail();
            Auth::guard('web')->login($collaborateur, false);
            $request->session()->regenerate();
        } catch (ModelNotFoundException $e) {
            abort(403, "Windows account '{$accountName}' not authorized");
        }

        return $next($request);
    }

    private function parseAccountName(string $windowsUser): string
    {
        if (str_contains($windowsUser, '\\')) {
            return last(explode('\\', $windowsUser));
        }
        
        if (str_contains($windowsUser, '@')) {
            return head(explode('@', $windowsUser));
        }

        return strtolower(trim($windowsUser));
    }
}