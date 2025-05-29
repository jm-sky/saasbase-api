<?php

namespace App\Services\MfLookup\Integrations;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class MfApiConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return 'https://wl-api.mf.gov.pl';
    }
}
