<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Collaborateur;

class LocalAuth
{
    public function handle(Request $request, Closure $next)
    {
        $accountName = env('DEV_IMPERSONATE_ACCOUNT');
        
        if (!$accountName) {
            abort(500, 'DEV_IMPERSONATE_ACCOUNT not configured in .env file');
        }

        if (Auth::guard('web')->check()) {
            $currentUser = Auth::guard('web')->user();
            if ($currentUser->account_name === $accountName) {
                return $next($request);
            }
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $user = Collaborateur::where('account_name', $accountName)->first();
        
        if (!$user) {
            abort(403, "User with account_name '{$accountName}' not found in database.");
        }
        
        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();
        
        return $next($request);
    }
}