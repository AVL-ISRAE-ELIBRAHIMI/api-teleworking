<?php

namespace App\Http\Middleware;


use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;


class EncryptCookies extends Middleware

{
    /**
     * Handle an incoming request.
     *
     * 
     * */
    protected $except = [
        //
    ];
}

