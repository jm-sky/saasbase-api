<?php

use App\Providers\AppServiceProvider;
use App\Providers\CommandServiceProvider;
use App\Providers\IbanServiceProvider;
use App\Providers\StripeServiceProvider;
use App\Providers\TelescopeServiceProvider;

return [
    AppServiceProvider::class,
    CommandServiceProvider::class,
    TelescopeServiceProvider::class,
    IbanServiceProvider::class,
    StripeServiceProvider::class,
];
