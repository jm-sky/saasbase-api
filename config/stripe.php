<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe Keys
    |--------------------------------------------------------------------------
    |
    | The Stripe publishable key and secret key give you access to Stripe's
    | API. The "publishable" key is typically used when interacting with
    | Stripe.js while the "secret" key accesses private API endpoints.
    |
    */
    'key'    => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Secret
    |--------------------------------------------------------------------------
    |
    | The Stripe webhook secret is used to verify that webhook events are
    | actually sent from Stripe. You can find this in your Stripe dashboard.
    |
    */
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Tolerance
    |--------------------------------------------------------------------------
    |
    | This option controls the tolerance in seconds for webhook signatures.
    | The default is 300 seconds (5 minutes).
    |
    */
    'webhook_tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),

    /*
    |--------------------------------------------------------------------------
    | Stripe API Version
    |--------------------------------------------------------------------------
    |
    | This option controls the Stripe API version to use. The default is the
    | latest version that is compatible with the installed Stripe SDK.
    |
    */
    'api_version' => env('STRIPE_API_VERSION', '2023-10-16'),
];
