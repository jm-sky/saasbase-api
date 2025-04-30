<?php

return [
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
