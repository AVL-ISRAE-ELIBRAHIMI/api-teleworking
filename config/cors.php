<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', '*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:8081'], // ton frontend
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
