<?php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Admins and staff are both in `users` (staff: user_type = 'staff'),
        // so one session guard serves both. Mobile apps use auth:sanctum.
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\Users::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ],

        // OPTIONAL: add password reset for staff if needed
        'staff' => [
            'provider' => 'staff',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],

];
