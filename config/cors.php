<?php

return [

    'paths' => ['*'],
        // 'api/*',
        // // 'sanctum/csrf-cookie',
        // 'login',
        // 'logout',
        // 'profil',
        // 'places',
        // 'salles',
        // 'reservations',
        // 'reservations/*',
        // 'availability/*',
        // 'seat-booking-type' // Ajoutez cette ligne
    // ],
    'allowed_methods' => ['*'],

    // 'allowed_origins' => ['*'], // ← ou l’URL de ton front
    'allowed_origins' => ['http://localhost:8080'],


    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
