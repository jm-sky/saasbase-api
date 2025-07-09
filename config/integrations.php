<?php

return [
    'azureAi' => [
        'endpoint' => env('AZURE_AI_ENDPOINT'),
        'key'      => env('AZURE_AI_KEY'),
        'region'   => env('AZURE_AI_REGION', 'eastus'),
    ],

    's3' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_BUCKET'),
    ],

    'regonApi' => [
        'key'      => env('REGON_API_KEY'),
        'endpoint' => env('REGON_API_ENDPOINT', 'https://wyszukiwarkaregon.stat.gov.pl/wsBIR/UslugaBIRzewnPubl.svc'),
    ],

    'googleCalendar' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri'  => env('GOOGLE_REDIRECT_URI'),
    ],

    'microsoftCalendar' => [
        'client_id'     => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'tenant_id'     => env('MICROSOFT_TENANT_ID'),
        'redirect_uri'  => env('MICROSOFT_REDIRECT_URI'),
    ],

    'jira' => [
        'url'      => env('JIRA_URL'),
        'username' => env('JIRA_USERNAME'),
        'token'    => env('JIRA_TOKEN'),
    ],

    'ksef' => [
        'nip'         => env('KSEF_NIP'),
        'token'       => env('KSEF_TOKEN'),
        'environment' => env('KSEF_ENVIRONMENT', 'test'),
    ],

    'eDelivery' => [
        'username' => env('EDELIVERY_USERNAME'),
        'password' => env('EDELIVERY_PASSWORD'),
        'endpoint' => env('EDELIVERY_ENDPOINT'),
    ],
];
