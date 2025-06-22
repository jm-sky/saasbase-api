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
        'tenants' => [
            'logo' => [
                'size'          => 256,
                'max_size'      => 2048, // 2MB
            ],
            'banner_image' => [
                'size'          => 1200,
                'max_size'      => 2048, // 2MB
            ],
            'pdf_logo' => [
                'size'          => 800,
                'max_size'      => 2048, // 2MB
            ],
            'email_header_image' => [
                'size'          => 600,
                'max_size'      => 2048, // 2MB
            ],
        ],
        'users' => [
            'avatar' => [
                'size' => 256,
            ],
        ],
    ],
];
