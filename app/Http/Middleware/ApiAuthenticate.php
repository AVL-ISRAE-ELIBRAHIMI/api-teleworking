<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticate
{
    /**
     * Handle an incoming request for API routes
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via session
        if (!Session::has('user.id')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please authenticate first.',
                'code' => 'AUTHENTICATION_REQUIRED'
            ], 401);
        }

        // Add user data to request for easy access in controllers
        $request->merge([
            'authenticated_user' => [
                'id' => Session::get('user.id'),
                'displayName' => Session::get('user.display_name'),
                'email' => Session::get('user.email'),
                'jobTitle' => Session::get('user.job_title'),
                'departement_id' => Session::get('user.departement_id'),
            ]
        ]);

        return $next($request);
    }
}