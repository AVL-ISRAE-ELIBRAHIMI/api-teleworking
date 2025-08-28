<?php

return [
    // Allow all paths since you're using web routes for your API
    'paths' => ['*'],

    'allowed_methods' => ['*'],

    // Add common Vue.js development server ports
    'allowed_origins' => [
        'http://localhost:8080',  // Default Vue CLI dev server
        'http://localhost:8082',  // Your current port
        'http://localhost:3000',  // Alternative dev port
        'http://localhost:5173',  // Vite dev server default
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // This is crucial for session-based authentication
    'supports_credentials' => true,
];