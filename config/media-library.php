<?php

return [
    'disk_name' => env('MEDIA_DISK', 's3'),

    'prefix' => '',

    'path_generator' => 
Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator::class,

    'url_generator' => null,

    'remote' => [
        'extra_headers' => [
            'CacheControl' => 'max-age=604800',
        ],
    ],
];
