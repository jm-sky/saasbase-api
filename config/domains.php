<?php

return [
    'domains' => [
        'contractors' => [
            'logo' => [
                'size'          => 256,
                'max_size'      => 2048, // 2MB
                'allowed_mimes' => [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ],
            ],
            'attachments' => [
                'max_size' => 10240, // 10MB
            ],
        ],
        'products' => [
            'logo' => [
                'size'          => 256,
                'max_size'      => 2048, // 2MB
                'allowed_mimes' => [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ],
            ],
        ],
        'users' => [
            'avatar' => [
                'size' => 256,
            ],
        ],
    ],
];
