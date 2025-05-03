<?php

return [

    'default_user' => [
        'first_name' => env('DEFAULT_USER_FIRST_NAME', 'Test'),
        'last_name'  => env('DEFAULT_USER_LAST_NAME', 'User'),
        'email'      => env('DEFAULT_USER_EMAIL', 'test@example.com'),
        'password'   => env('DEFAULT_USER_PASSWORD', 'Secret123!'),
        'is_admin'   => env('DEFAULT_USER_IS_ADMIN', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Registration Settings
    |--------------------------------------------------------------------------
    |
    | Here you may configure your registration settings such as requiring admin
    | approval for new registrations.
    |
    */

    'registration' => [
        'require_admin_approval' => env('USER_REQUIRE_ADMIN_APPROVAL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Settings
    |--------------------------------------------------------------------------
    |
    | Define the available user statuses and their descriptions.
    |
    */

    'statuses' => [
        'active' => [
            'description' => 'User is active and can access the system',
        ],
        'pending' => [
            'description' => 'User is pending admin approval',
        ],
        'blocked' => [
            'description' => 'User has been blocked from accessing the system',
        ],
    ],
];
