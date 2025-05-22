<?php

return [

    // 'paths' => ['api/*', 'login', 'sanctum/csrf-cookie'],
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'reservations/*'],

    'allowed_methods' => ['*'],

    // 'allowed_origins' => ['*'], // ← ou l’URL de ton front
    'allowed_origins' => ['http://localhost:8080'],


    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
