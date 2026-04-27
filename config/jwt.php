<?php

return [
    'secret'     => env('JWT_SECRET'),
    'algorithm'  => 'HS256',
    'expires_in' => env('JWT_EXPIRES_IN', 3600), // seconds
];
