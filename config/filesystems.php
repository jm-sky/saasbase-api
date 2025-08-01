<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    'use_s3_temporary_urls' => env('USE_S3_TEMPORARY_URL', 'production' === env('APP_ENV')),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
            'report' => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ],

        's3' => [
            'driver'                  => 's3',
            'key'                     => env('S3_ACCESS_KEY'),
            'secret'                  => env('S3_SECRET_KEY'),
            'region'                  => env('S3_REGION'),
            'bucket'                  => env('S3_BUCKET'),
            'endpoint'                => env('S3_ENDPOINT'),
            'use_path_style_endpoint' => env('S3_USE_PATH_STYLE_ENDPOINT', false),
            'throw'                   => false,
            'report'                  => false,
        ],

        'minio' => [
            'driver'                  => 's3',
            'key'                     => env('MINIO_ROOT_USER', 'sail'),
            'secret'                  => env('MINIO_ROOT_PASSWORD', 'password'),
            'endpoint'                => env('MINIO_ENDPOINT_URL', 'http://minio:9000'),
            'region'                  => 'us-east-1',
            'bucket'                  => env('MINIO_BUCKET', 'saasbase'),
            'use_path_style_endpoint' => true,
            'throw'                   => false,
            'report'                  => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
