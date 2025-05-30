<?php

return [
    /*
    |--------------------------------------------------------------------------
    | E-Doreczenia Providers
    |--------------------------------------------------------------------------
    |
    | Configuration for e-doreczenia providers. Each provider should have its
    | own API credentials and settings.
    |
    */

    'providers' => [
        'edo_post' => [
            'api_key'    => env('EDOPOST_API_KEY'),
            'api_secret' => env('EDOPOST_API_SECRET'),
            'api_url'    => env('EDOPOST_API_URL', 'https://api.edopost.pl/v1'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Provider
    |--------------------------------------------------------------------------
    |
    | The default provider to use when no specific provider is selected.
    |
    */

    'default_provider' => env('EDORECZENIA_DEFAULT_PROVIDER', 'edo_post'),

    /*
    |--------------------------------------------------------------------------
    | Certificate Settings
    |--------------------------------------------------------------------------
    |
    | Settings for certificate handling and validation.
    |
    */

    'certificates' => [
        'storage_path'                 => env('EDORECZENIA_CERTIFICATES_PATH', 'certificates'),
        'expiration_notification_days' => env('EDORECZENIA_CERTIFICATE_EXPIRATION_NOTIFICATION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Settings
    |--------------------------------------------------------------------------
    |
    | Settings for message handling and synchronization.
    |
    */

    'messages' => [
        'sync_interval'       => env('EDORECZENIA_SYNC_INTERVAL', 60), // minutes
        'max_attachments'     => env('EDORECZENIA_MAX_ATTACHMENTS', 10),
        'max_attachment_size' => env('EDORECZENIA_MAX_ATTACHMENT_SIZE', 10 * 1024 * 1024), // 10MB
    ],
];
