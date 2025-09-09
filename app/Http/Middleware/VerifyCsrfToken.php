<?php
namespace App\Http\Middleware;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
    'api/*',
    ];
    
    // protected function tokensMatch($request)
    // {
    //     // Skip CSRF check for API routes
    //     if ($request->is('api/*')) {
    //         return true;
    //     }
        
    //     return parent::tokensMatch($request);
    // }
}