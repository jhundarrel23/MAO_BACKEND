<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // ✅ Must be explicit origin (no *)
   'allowed_origins' => [
    'http://localhost:3000',
    'https://9jcdqlss-3000.asse.devtunnels.ms', // 👈 add your tunnel here
],


    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ✅ Must be true if using withCredentials in axios
    'supports_credentials' => true,

];
