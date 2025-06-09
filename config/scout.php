<?php

return [
    'enabled' => env('SCOUT_ENABLED', false),
    'driver'  => env('SCOUT_DRIVER', 'meilisearch'),
    'queue'   => env('SCOUT_QUEUE', true),
    'chunk'   => [
        'searchable'   => 500,
        'unsearchable' => 500,
    ],
    'soft_delete' => false,
    'identify'    => env('SCOUT_IDENTIFY', false),
    'meilisearch' => [
        'host'           => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key'            => env('MEILISEARCH_KEY', 'masterKey'),
        'index-settings' => [
            'Contractor' => [
                'filterableAttributes' => ['tenant_id', 'is_active'],
                'sortableAttributes'   => ['created_at', 'updated_at'],
            ],
            'Product' => [
                'filterableAttributes' => ['tenant_id', 'unit_id', 'vat_rate_id'],
                'sortableAttributes'   => ['created_at', 'updated_at'],
            ],
            'Invoice' => [
                'filterableAttributes' => ['tenant_id', 'status', 'due_date'],
                'sortableAttributes'   => ['created_at', 'updated_at', 'due_date'],
            ],
            'User' => [
                'filterableAttributes' => ['tenant_id', 'is_active'],
                'sortableAttributes'   => ['created_at', 'updated_at'],
            ],
            'Contact' => [
                'filterableAttributes' => ['tenant_id', 'contactable_type', 'contactable_id'],
                'sortableAttributes'   => ['created_at', 'updated_at'],
            ],
        ],
    ],
];
