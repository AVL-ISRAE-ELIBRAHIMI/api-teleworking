<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', '*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:8081', 'http://127.0.0.1:8081'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
