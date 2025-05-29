<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class IbanServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once base_path('vendor/globalcitizen/php-iban/php-iban.php');
    }
}
